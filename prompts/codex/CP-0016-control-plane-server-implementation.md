# CP-0016 — Control Plane Server MVP (implementation prompt)

## Goal
Bootstrap a separate Laravel repo that implements the Control Plane server MVP described in `docs/control-plane-mvp.md`.

## Scope
- Migrations for `instances`, `modules`, `entitlements`, `keys`
- REST API endpoints:
  - POST /api/v1/instances/register
  - POST /api/v1/instances/heartbeat
  - POST /api/v1/licenses/refresh
- JWT RS256 issuance with `kid` and rotation support
- Minimal Filament admin panel for instances + entitlements
- Audit logging for register/heartbeat/refresh

## Constraints
- No inbound webhooks
- No full billing system
- No agent implementation (client side is separate)

## Validation
- `php artisan test`
- Endpoint tests for register/heartbeat/refresh

## Git
- Commit: "CP-0016: control plane server MVP implementation"
