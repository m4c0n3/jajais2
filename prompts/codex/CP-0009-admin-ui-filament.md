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
Použiť Filament Panels (open-source). Inštalácia: `php artisan filament:install --panels`, provider: `app/Providers/Filament/AdminPanelProvider.php`. :contentReference[oaicite:1]{index=1}

## Scope
### In scope
- Inštalácia Filament Panel Builder:
  - `composer require filament/filament:"^3.3" -W`
  - `php artisan filament:install --panels` :contentReference[oaicite:2]{index=2}
- Filament prístupová kontrola:
  - `App\Models\User` implementuje `Filament\Models\Contracts\FilamentUser`
  - `canAccessPanel()` povolí iba používateľom s rolou `super-admin` alebo permission `admin.access` :contentReference[oaicite:3]{index=3}
- Filament Resources:
  1) WebhookEndpointResource (CRUD)
     - polia: name, url, is_active, events (multi-select), timeout, max_attempts, backoff_seconds, headers
     - secret:
       - maskovať v tabuľke (••••••)
       - v edit forme: "Regenerate secret"
     - akcie:
       - "Test" (spustí test delivery cez existujúci mechanizmus z CP-0008)
       - enable/disable toggle
  2) WebhookDeliveryResource (read-only + retry)
     - list s filtrami: status, endpoint, event, date range
     - detail: payload (pretty JSON), response code, last_error, attempt, next_attempt_at, delivered_at, correlation_id
     - akcie:
       - "Retry now" (iba s permission `webhooks.replay`)
- RBAC permissions:
  - `admin.access`
  - `webhooks.view`
  - `webhooks.manage`
  - `webhooks.replay`
- Audit log:
  - webhook.endpoint_created / updated / deleted
  - webhook.delivery_retried
  - webhook.endpoint_tested

### Out of scope
- UI pre správu rolí/permissions (iba baseline prístup)
- Incoming webhooks

## DoD
- [ ] `/admin` panel funguje (login)
- [ ] Prístup limitovaný (`admin.access` alebo `super-admin`)
- [ ] Endpoint CRUD + test + regenerate secret
- [ ] Deliveries view + retry gated
- [ ] Secret sa nezobrazuje v list view a nie je v logoch
- [ ] Audit log sa zapisuje pri kľúčových UI akciách
- [ ] Testy prechádzajú

## Validácia
- `php artisan migrate`
- `php artisan rbac:sync`
- vytvoriť admin user (napr. Filament make user command)
- otvoriť `/admin` a otestovať CRUD + retry
- `php artisan test`

## Povinný Git workflow
Po úspešnom dokončení:
- commit: "CP-0009: admin UI for webhooks (Filament)"
- push na aktuálny branch
