# Agent Module

## Setup

Environment:
- `CONTROL_PLANE_ENABLED=true`
- `CONTROL_PLANE_URL=https://control-plane.example`
- `CONTROL_PLANE_TOKEN=...`
- `CONTROL_PLANE_REGISTRATION_TOKEN=...` (bootstrap only)
- `CONTROL_PLANE_TIMEOUT=5`
- `CONTROL_PLANE_RETRY=2`
- `CONTROL_PLANE_RETRY_BACKOFF_MS=200`
- `CONTROL_PLANE_RETRY_MAX_SECONDS=10`
- `CONTROL_PLANE_JWT_PUBLIC_KEY=...` (PEM)
- `CONTROL_PLANE_JWT_PUBLIC_KEY_PATH=/path/to/public.pem`
- `CONTROL_PLANE_JWT_ISSUER=...`
- `CONTROL_PLANE_JWT_AUDIENCE=...` (or leave empty to use instance UUID)
- `CONTROL_PLANE_SCHEDULE_ENABLED=true`
- `CONTROL_PLANE_HEARTBEAT_CRON=* * * * *`
- `CONTROL_PLANE_LICENSE_REFRESH_CRON=0 * * * *`

Enable module (once module system is in place):
- `php artisan module:discover`
- `php artisan module:enable Agent`

Commands:
- `php artisan agent:register`
- `php artisan agent:heartbeat`
- `php artisan agent:license-refresh`
- `php artisan agent:status`

Scheduler:
- Ensure Laravel scheduler is running (cron) so `agent:heartbeat` and `agent:license-refresh` execute automatically.

Notes:
- Tokens are never logged.
- If AuditService exists, agent commands emit `agent.*` audit events.

Key rotation:
- Publish the next public key to all instances (env or file path).
- Rotate signing keys in Control Plane.
- Remove the old public key after all instances are updated.
