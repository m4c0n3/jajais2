# Cloudron Smoke Tests

## Scenario A: Client mode install
1) Deploy the Cloudron app.
2) Open `/install` and choose `client`.
3) Verify `/health` returns 200.
4) Login to `/admin` and confirm admin access.
5) Check storage is writable (upload a webhook test delivery).
6) Verify queue and scheduler are running (check log for queue/scheduler activity).

## Scenario B: Control-plane mode install
1) Deploy the Cloudron app.
2) Open `/install` and choose `control-plane`.
3) Verify `/health` returns 200.
4) Login to `/admin` and confirm Control Plane resources are visible.
5) Run `php artisan control-plane:key:rotate`.
6) Verify `/api/v1/instances/register` returns a secret.

## Checklist
- `/health` returns 200
- `/install` is available before init and blocked after init
- Admin login works (super-admin/admin.access)
- DB migrations ran successfully
- Storage is RW (`storage/` on /app/data)
- Queue driver is healthy
- Scheduler is running
