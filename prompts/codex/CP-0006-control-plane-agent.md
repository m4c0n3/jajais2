# CP-0006 — Control Plane kontrakt + Agent modul (heartbeat + license refresh)

## Meta
- ID: CP-0006
- Version: 1.0.0
- Title: Control Plane kontrakt + Agent modul (heartbeat + license refresh)
- Status: ready
- Date: 2026-01-16

## Context
Aplikácia má byť pripojená na centralizovaný riadiaci systém (Control Plane), ktorý:
- monitoruje funkčnosť (heartbeat, health)
- kontroluje platnosť licencií modulov a poskytuje entitlement tokeny

Máme:
- moduly v /modules
- lokálne licencovanie cache (license_tokens + LicenseService) z CP-0003
- modulový boot a príkazy (CP-0004)
- RBAC + audit (CP-0005) — audit chceme využiť aj pre agent príkazy

Teraz dodáme:
- minimálny kontrakt API (dokumentácia)
- modul Agent v /modules/Agent:
  - artisan príkazy na register / heartbeat / license-refresh
  - HTTP klient na Control Plane
  - ukladanie “instance identity” lokálne (instance_uuid)
  - aktualizáciu license_tokens cez Control Plane response

## Scope
### In scope
- Dokumentovať Control Plane kontrakt (API endpoints + payloady) do `docs/control-plane-contract.md`
- Pridať modul `/modules/Agent` (self-contained):
  - module.json + provider
  - src/ (services/commands)
  - database/migrations (instance_state tabuľka)
  - docs/README.md (ako nastaviť agent)
- Pridať DB tabuľku `instance_state` (lokálna identita inštancie):
  - instance_uuid, registered_at, last_heartbeat_at, last_license_refresh_at, last_error
- Konfigurácia agenta cez env/config:
  - CONTROL_PLANE_URL
  - CONTROL_PLANE_TOKEN (API token pre inštanciu) alebo CONTROL_PLANE_REGISTRATION_TOKEN (bootstrap)
  - CONTROL_PLANE_TIMEOUT, CONTROL_PLANE_RETRY
- Implementovať príkazy:
  - `agent:register` (bootstrap/registrácia)
  - `agent:heartbeat` (pošle heartbeat payload)
  - `agent:license-refresh` (stiahne nový entitlement token a uloží do license_tokens)
- Implementovať Agent HTTP klient (Laravel Http::) s timeout + retry + error handling
- Napojiť scheduled spúšťanie (voliteľné, ale odporúčané):
  - provider registruje schedule:
    - heartbeat každú 1 min (configovateľné)
    - license refresh každú 1 hod (configovateľné)
- Základné testy s Http::fake:
  - heartbeat posiela request na správny endpoint s očakávaným payloadom (aspoň kľúče)
  - license-refresh uloží nový záznam do license_tokens (alebo aktualizuje “active” token)
- Audit log minimum:
  - pri spustení príkazov `agent:*` zapísať audit action:
    - agent.register
    - agent.heartbeat
    - agent.license_refresh

### Out of scope
- Implementácia Control Plane server aplikácie
- mTLS a podpisovanie requestov (iba TODO hook; teraz Bearer token)
- Kompletné health metriky (p95, queue backlog, tracing)
- UI dashboard pre inštancie

## Návrh riešenia
### Čo navrhujem
1) Control Plane kontrakt (dokument):
- `POST /api/v1/instances/register`
- `POST /api/v1/instances/{instance_uuid}/heartbeat`
- `POST /api/v1/instances/{instance_uuid}/license/refresh`

2) Agent modul:
- `InstanceStateRepository` (jediný záznam, resp. firstOrCreate)
- `ControlPlaneClient` (Http client)
- `AgentService`:
  - buildHeartbeatPayload()
  - sendHeartbeat()
  - refreshLicenseToken() -> uložiť do license_tokens

3) Licenčný refresh:
- Control Plane vráti napr.:
  - `token` (string)
  - `valid_to` (ISO8601)
  - `grace_to` (ISO8601 nullable)
  - `parsed` (optional json)
Agent uloží do `license_tokens` (kompatibilné s CP-0003)

### Prečo
- Outbound komunikácia je NAT/firewall friendly.
- Lokálne overovanie licencií ostáva v LicenseService (rychlé, stabilné).
- Agent modul je prenášateľný a izolovaný.

### Bezpečnostné minimum
- HTTPS povinné (CONTROL_PLANE_URL musí byť https v prod; v dev povolené http)
- Authorization: `Bearer <CONTROL_PLANE_TOKEN>`
- retry/backoff, logovanie chýb bez úniku tokenu do logov

## Dopady
- Bezpečnosť: pribudne outbound integrácia, treba dbať na secret handling.
- Výkon: minimálny (periodické joby).
- DB: nová tabuľka instance_state.
- Prevádzka: treba mať spustený Laravel scheduler (cron).

## Predpoklady a závislosti
- CP-0003: license_tokens + LicenseService existuje
- CP-0004: modulové bootovanie existuje (Agent modul sa bootne iba keď je enabled)
- CP-0005: AuditService existuje (ak nie, audit logovanie v agente spraviť “best effort” bez hard dependency)

## Úlohy pre Codex (kroky)
1) Pridaj dokument `docs/control-plane-contract.md`:
   - endpointy
   - príklady request/response payloadov
   - error codes (401/403/422/500)
2) Vytvor modul `/modules/Agent` so štruktúrou:
   - module.json (id: agent, license_required: false, provider FQCN)
   - src/Providers/AgentServiceProvider.php
   - src/Services/ControlPlaneClient.php
   - src/Services/AgentService.php
   - src/Console/Commands/AgentRegisterCommand.php
   - src/Console/Commands/AgentHeartbeatCommand.php
   - src/Console/Commands/AgentLicenseRefreshCommand.php
   - database/migrations/*create_instance_state_table.php
   - docs/README.md
   - config/agent.php (v module, mergeConfigFrom v provider)
3) Migrácia `instance_state`:
   - id (pk)
   - instance_uuid (uuid, unique)
   - registered_at (nullable datetime)
   - last_heartbeat_at (nullable datetime)
   - last_license_refresh_at (nullable datetime)
   - last_error (nullable text)
   - timestamps
4) `agent:register`:
   - vygeneruje uuid, ak neexistuje
   - zavolá register endpoint (ak je CONTROL_PLANE_REGISTRATION_TOKEN nastavený)
   - uloží registered_at
   - audit log agent.register
5) `agent:heartbeat`:
   - zostaví payload:
     - instance_uuid
     - app_env, app_version (ak dostupné), php_version
     - modules summary (id, version, enabled, license_required)
     - license meta (valid_to/grace_to/is_valid/is_grace z LicenseService)
     - timestamp
   - pošle na heartbeat endpoint
   - uloží last_heartbeat_at
   - audit log agent.heartbeat
6) `agent:license-refresh`:
   - zavolá license refresh endpoint
   - uloží token do license_tokens (nový záznam)
   - uloží last_license_refresh_at
   - audit log agent.license_refresh
7) Schedule (voliteľne, ale implementuj):
   - v provider boot: afterResolving(Schedule::class, ...)
   - heartbeat everyMinute, license refresh hourly (configovateľné)
8) Testy:
   - Http::fake pre heartbeat a refresh
   - overiť, že refresh uložil token do DB
9) Dokumentácia:
   - env premenné
   - ako enable-núť Agent modul
   - ako spustiť scheduler

## Akceptačné kritériá (DoD)
- [ ] Existuje `docs/control-plane-contract.md`
- [ ] Modul Agent existuje v /modules/Agent a je self-contained
- [ ] `php artisan agent:register` funguje (vytvorí instance_uuid a uloží do DB)
- [ ] `php artisan agent:heartbeat` odošle request (testované cez Http::fake)
- [ ] `php artisan agent:license-refresh` uloží token do license_tokens (testované)
- [ ] Scheduler hook je prítomný (neblokuje app, dá sa vypnúť configom)
- [ ] Testy prechádzajú

## Validácia
- Príkazy:
  - `php artisan migrate`
  - `php artisan module:discover`
  - `php artisan module:enable Agent` (ak je modulový systém pripravený)
  - `php artisan agent:register`
  - `php artisan agent:heartbeat`
  - `php artisan agent:license-refresh`
  - `php artisan test`
- Očakávaný výsledok:
  - migrácie ok
  - agent príkazy bežia (ak je Control Plane nedostupný, majú zrozumiteľnú chybu a necrashnú)
  - testy zelené

## Rollback plán
- `php artisan migrate:rollback` (posledná migrácia)
- `git revert <commit>`

## Report pre človeka
- Zmenené/pridané súbory: docs contract, Agent modul súbory, migrácia, testy, docs
- Ako otestovať: Validácia príkazy

## Povinný Git workflow (platí pre tento prompt)
Po úspešnom dokončení implementácie a validácii:
- vždy vykonaj `git status`
- ak existujú zmeny, vykonaj:
  - `git add` iba relevantných súborov
  - commit message: "CP-0006: control plane contract and agent module"
  - `git push` na aktuálny branch (ak je remote nastavený)
- ak nie sú žiadne zmeny, nevytváraj prázdny commit, ale uveď to v reporte
