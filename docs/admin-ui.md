# Admin UI (Filament)

## Setup
- Install dependencies: `composer install`
- Run migrations: `php artisan migrate`
- Create an admin user: `php artisan make:filament-user`
- Sync permissions: `php artisan rbac:sync`

## Access
- Admin panel is available at `/admin`.
- Access requires role `super-admin` or permission `admin.access`.

## Webhooks
- `webhooks.view` allows viewing endpoints/deliveries.
- `webhooks.manage` allows CRUD + test.
- `webhooks.replay` allows retrying deliveries.

Secrets are never shown in list views and are not logged.
