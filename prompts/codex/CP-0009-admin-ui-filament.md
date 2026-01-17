# CP-0009 — Admin UI (Filament) pre Webhooks + Deliveries + RBAC

## Meta
- ID: CP-0009
- Version: 1.0.0
- Title: Admin UI (Filament) pre Webhooks + Deliveries + RBAC
- Status: ready
- Date: 2026-01-17

## Context
Máme outgoing webhooks engine (CP-0008), RBAC (CP-0005) a audit log minimum.
Teraz potrebujeme admin rozhranie, aby sa dali:
- spravovať webhook endpointy (URL, secret, eventy, aktívny stav)
- prezerať deliveries (pending/sent/failed, response, error, retries)
- robiť operácie (retry failed delivery, test endpoint)
- mať prístup riadený oprávneniami (Spatie permissions)

## Tech decision
Použiť Filament Panels (open-source) ako admin panel framework.
Pozn.: Filament install vytvára panel provider a panel beží na `/admin`. :contentReference[oaicite:1]{index=1}

## Scope
### In scope
- Inštalácia Filament Panel Builder:
  - `composer require filament/filament:"^3.3" -W`
  - `php artisan filament:install --panels`
  - overiť registráciu provideru v `bootstrap/providers.php` (Laravel 11+). :contentReference[oaicite:2]{index=2}
- Filament prístupová kontrola:
  - `App\Models\User` implementuje `Filament\Models\Contracts\FilamentUser`
  - `canAccessPanel()` povolí iba používateľom s rolou `super-admin` alebo permission `admin.access`
  - vytvorenie používateľa cez `php artisan make:filament-user`. :contentReference[oaicite:3]{index=3}
- Filament Resources:
  1) WebhookEndpointResource (CRUD)
     - polia: name, url, is_active, events (multi-select), timeout, max_attempts, backoff_seconds, headers
     - secret:
       - ukladať do DB ako string
       - v tabuľke maskovať (napr. "••••••")
       - v edit forme: tlačidlo "Regenerate secret" (vygeneruje nový)
       - nikdy nezobrazovať secret v logoch
     - akcie:
       - "Test" (spustí test delivery cez existujúci webhook dispatch/test mechanizmus)
       - enable/disable toggle
  2) WebhookDeliveryResource (read-only + retry)
     - list s filtrami: status, endpoint, event, date range
     - detail: payload (pretty JSON), response code, last_error, attempt, next_attempt_at, delivered_at, correlation_id
     - akcie:
       - "Retry now" (iba s permission `webhooks.replay`)
       - "View payload"
- RBAC permissions (zaviesť a používať):
  - `admin.access`
  - `webhooks.view`
  - `webhooks.manage`
  - `webhooks.replay`
- Prepojenie na existujúci RBAC sync:
  - doplniť permissions do manifestu aktívneho modulu (preferovane HelloWorld alebo nový AdminUi modul)
  - `php artisan rbac:sync` ich vytvorí
- Audit log:
  - logovať minimálne:
    - webhook.endpoint_created / updated / deleted
    - webhook.delivery_retried
    - webhook.endpoint_tested

### Out of scope
- Kompletný admin panel pre všetky časti systému (iba webhooks + deliveries)
- Incoming webhooks
- Multi-tenant UI
- Kompletný UI pre správu rolí/permissions (stačí baseline)

## Implementačné zásady
- Bezpečnosť:
  - secret nikdy nelogovať ani nezobrazovať v list view
  - URL validovať (https preferované)
  - retry akcie chrániť permission `webhooks.replay`
- UX:
  - deliveries view má mať rýchle filtre (status=failed)
  - endpoint list má mať jasný "Active" stĺpec
- Modulárnosť:
  - Filament panel provider môže zostať v app/Providers/Filament, ale webhook resources môžu byť v app/Filament alebo v module.
  - Ak sa použije modulová cesta, zabezpečiť ich registráciu v panel provideri.

## Úlohy pre Codex (kroky)
1) Nainštalovať Filament + `filament:install --panels`
2) Upraviť `App\Models\User`:
   - implementovať `FilamentUser` a `canAccessPanel()`
3) Pridať RBAC permissions (admin.access, webhooks.*) tak, aby ich vedel `rbac:sync` vytvoriť
4) Vytvoriť Filament Resources:
   - WebhookEndpointResource (CRUD + actions)
   - WebhookDeliveryResource (read-only + retry action)
5) Prepojiť actions na existujúce služby z CP-0008:
   - test endpoint -> vytvor delivery + dispatch job
   - retry -> requeue delivery/job
6) Audit logovať UI akcie (best effort cez AuditService)
7) Dokumentácia:
   - `docs/admin-ui.md` (setup, vytvorenie používateľa, RBAC permissions)
   - doplniť do README: ako otvoriť /admin

## Akceptačné kritériá (DoD)
- [ ] `/admin` panel funguje (login)
- [ ] Prístup do panelu je limitovaný (`admin.access` alebo `super-admin`)
- [ ] Endpointy sa dajú spravovať cez UI (create/edit/disable/test)
- [ ] Deliveries sa dajú prezerať a retry-nuť (RBAC gated)
- [ ] Secret sa nezobrazuje v list view a nie je v logoch
- [ ] Audit log sa zapisuje pri kľúčových UI akciách
- [ ] Testy prechádzajú

## Validácia
- `php artisan migrate`
- `php artisan rbac:sync`
- `php artisan make:filament-user` (lokálne)
- Otvoriť `/admin` a overiť:
  - endpoint CRUD
  - delivery list + retry
- `php artisan test`

## Rollback plán
- `composer remove filament/filament`
- odstrániť Filament provider + resources
- `git revert <commit>`

## Povinný Git workflow
Po úspešnom dokončení:
- commit: "CP-0009: admin UI for webhooks (Filament)"
- push na aktuálny branch
