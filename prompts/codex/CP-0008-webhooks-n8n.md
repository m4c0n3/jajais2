# CP-0008 — Outgoing Webhooks engine + n8n integrácia (HMAC, retry, DLQ)

## Meta
- ID: CP-0008
- Version: 1.0.0
- Title: Outgoing Webhooks engine + n8n integrácia
- Status: ready
- Date: 2026-01-17

## Context
Projekt má podporovať API, webhooks a automatizáciu cez n8n.
Máme:
- modulový systém (discovery/boot/enable/disable)
- licencie (token cache + RS256 verify)
- audit log minimum
- agent heartbeat/refresh

Teraz potrebujeme univerzálny systém pre odosielanie outgoing webhookov na externé integrácie (najmä n8n):
- definovanie webhook endpointov (URL, secret, eventy)
- spoľahlivé doručovanie (queue + retry + DLQ)
- bezpečnosť (HMAC podpis + timestamp + replay protection)
- observabilita (log, status, posledná chyba)

## Scope
### In scope
- DB tabuľky:
  - `webhook_endpoints` (konfigurácia endpointov)
  - `webhook_deliveries` (log doručení + retry state)
- Modely + services:
  - `WebhookEndpoint` (active, url, secret, events, headers)
  - `WebhookDispatcher` (enqueue delivery)
  - `WebhookSender` (HTTP send, verify response, retry)
  - `WebhookSignature` (HMAC SHA-256 podpis)
- Queue job:
  - `SendWebhookDeliveryJob` (retry/backoff, max attempts, DLQ)
- Udalosti (events), ktoré budeme emitovať minimálne:
  - `module.enabled`
  - `module.disabled`
  - `license.updated` (po úspešnom license-refresh)
  - `rbac.synced`
  - `agent.heartbeat_failed` (voliteľné, ak existuje signal)
- Artisan príkazy:
  - `webhook:list`
  - `webhook:test <endpoint_id> --event=...`
  - `webhook:retry <delivery_id>`
  - `webhook:flush --failed` (voliteľné)
- Dokumentácia:
  - ako vytvoriť endpoint
  - aký je payload a hlavičky
  - n8n workflow príklad + verifikácia podpisu

### Out of scope
- Incoming webhooks do aplikácie (iba outgoing)
- UI admin panel (konfigurácia cez DB/seed/CLI je ok)
- Komplexné transformácie payloadov (len základný JSON payload)

## Návrh riešenia

### Tabuľka webhook_endpoints
- id (pk)
- name (string)
- url (string)
- is_active (bool)
- secret (string, encrypted at rest ak je jednoduché; inak aspoň masked v logs)
- events (json array of strings)
- headers (json nullable) — custom headers
- timeout_seconds (int default 10)
- max_attempts (int default 10)
- backoff_seconds (json nullable) — napr. [10,30,60,300,...]
- last_success_at (datetime nullable)
- last_failure_at (datetime nullable)
- last_failure_reason (text nullable)
- timestamps

### Tabuľka webhook_deliveries
- id (pk)
- webhook_endpoint_id (fk)
- event (string)
- payload (json)
- status (string: pending, sending, delivered, failed)
- attempt (int)
- next_attempt_at (datetime nullable)
- last_response_code (int nullable)
- last_error (text nullable)
- delivered_at (datetime nullable)
- correlation_id (uuid string) — trace naprieč systémami
- timestamps

### Podpis (HMAC)
- Hlavičky:
  - `X-Webhook-Event: <event>`
  - `X-Webhook-Id: <correlation_id>`
  - `X-Webhook-Timestamp: <unix_epoch_seconds>`
  - `X-Webhook-Signature: v1=<hex_hmac_sha256>`
- Signature base string:
  - `v1:<timestamp>:<raw_body>`
- HMAC secret: endpoint.secret
- Replay protection (na strane príjemcu): odmietnuť ak timestamp je starší ako napr. 300s

### Payload (JSON)
Základný formát:
```json
{
  "id": "<correlation_id>",
  "event": "module.enabled",
  "occurred_at": "2026-01-17T12:34:56Z",
  "actor": { "type": "user|system", "id": 123 },
  "data": { ... event specific ... }
}
```

### Retry/DLQ
- Job používa queue (database alebo redis).
- Retry na 429/5xx; fail fast na 4xx (okrem 429).
- Po max_attempts -> status=failed (DLQ) a zapísať last_error.

### Integrácia s existujúcimi akciami
- `module:enable/disable`: po úspechu emitnúť event
- `agent:license-refresh`: po úspechu emitnúť `license.updated`
- `rbac:sync`: emitnúť `rbac.synced`
- Fail eventy: `agent.heartbeat_failed` (ak je dostupné)

## Dopady
- DB: nové tabuľky
- Prevádzka: queue worker + scheduler (ak používame delayed next_attempt_at)
- Bezpečnosť: HMAC podpis, redakcia secretov v logoch

## Predpoklady a závislosti
- Queue je nakonfigurovaná (aspoň `database` driver)
- Audit log existuje (voliteľne logovať aj webhook events)

## Úlohy pre Codex (kroky)
1) Migrácie: webhook_endpoints, webhook_deliveries
2) Modely + policies (minimálne)
3) WebhookSignature helper (HMAC SHA-256)
4) WebhookDispatcher:
   - `dispatch(string $event, array $data, ?array $actor=null)`
   - vyberie endpointy, ktoré majú event v `events` a sú active
   - vytvorí delivery záznamy a dispatchne joby
5) Job `SendWebhookDeliveryJob`:
   - pošle HTTP POST na endpoint.url
   - nastaví hlavičky + signature
   - aktualizuje delivery status/attempt/response
   - pri fail: nastaví next_attempt_at podľa backoff alebo default exponential
6) Hooky do existujúcich commandov:
   - module enable/disable
   - rbac:sync
   - agent:license-refresh
7) Artisan príkazy:
   - webhook:list
   - webhook:test (vytvorí test delivery so sample payload)
   - webhook:retry (zoberie failed delivery a requeue)
8) Testy:
   - unit test signature (stabilný podpis pre známy body+timestamp)
   - feature test: dispatch vytvorí delivery a job sa pošle (Http::fake)
   - feature test: retry na 429/500 a success update
9) Dokumentácia + n8n:
   - `docs/webhooks.md` (konfigurácia, payload, headers)
   - `docs/n8n-webhook-receiver.md`:
     - n8n webhook node
     - code node na overenie HMAC a timestamp (pseudo-kód + JS snippet)
     - príklad mapovania na ďalší workflow

## Akceptačné kritériá (DoD)
- [ ] DB migrácie pre webhooks sú prítomné a prebehnú
- [ ] Endpointy sa dajú uložiť do DB a `WebhookDispatcher` ich vyberie podľa eventu
- [ ] Delivery sa odošle s HMAC podpisom a základnými hlavičkami
- [ ] Retry funguje (testované na 429/5xx)
- [ ] CLI príkazy fungujú (list/test/retry)
- [ ] Dokumentácia pre n8n receiver existuje
- [ ] Testy prechádzajú

## Validácia
- `php artisan migrate`
- vytvoriť endpoint (seed/DB insert) a spustiť `webhook:test <id> --event=module.enabled`
- `php artisan test`

## Rollback plán
- `php artisan migrate:rollback`
- `git revert <commit>`

## Povinný Git workflow
Po dokončení:
- commit: "CP-0008: outgoing webhooks engine and n8n integration"
- push na aktuálny branch
