# Control Plane Repo Bootstrap Checklist

## Repository setup
- New Laravel project (PHP 8.4+)
- Configure database and queue (database/redis)
- Add Sanctum for API auth
- Add Filament for admin UI (minimal)

## Data model
- Create migrations for `instances`, `modules`, `entitlements`, `keys`
- Add audit log table and service

## API
- Implement routes for:
  - `POST /api/v1/instances/register`
  - `POST /api/v1/instances/heartbeat`
  - `POST /api/v1/licenses/refresh`
- Validate payloads and authenticate with instance secret
- Add rate limiting and audit logging

## JWT issuance
- Implement RS256 key management with `kid`
- Provide rotation workflow (add new key, keep overlap, retire old)

## Admin UI
- Instances list/detail
- Entitlements management
- Key rotation view

## Ops
- Health endpoint
- System status command
- CI workflow
