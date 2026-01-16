# Agent Module

## Setup

Environment:
- `CONTROL_PLANE_ENABLED=true`
- `CONTROL_PLANE_URL=https://control-plane.example`
- `CONTROL_PLANE_TOKEN=...`
- `CONTROL_PLANE_REGISTRATION_TOKEN=...` (bootstrap only)
- `CONTROL_PLANE_TIMEOUT=5`
- `CONTROL_PLANE_RETRY=2`
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

Scheduler:
- Ensure Laravel scheduler is running (cron) so `agent:heartbeat` and `agent:license-refresh` execute automatically.

Notes:
- Tokens are never logged.
- If AuditService exists, agent commands emit `agent.*` audit events.
