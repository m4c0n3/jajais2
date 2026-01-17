# Control Plane Modules

## Modules
- ControlPlaneCore (API + DB)
- ControlPlaneAdmin (Filament resources)

## Endpoints
- POST `/api/v1/instances/register`
- POST `/api/v1/instances/heartbeat`
- POST `/api/v1/licenses/refresh`

## Key management
- `php artisan control-plane:key:list`
- `php artisan control-plane:key:rotate`

## Configuration
- `CONTROL_PLANE_ISSUER`
- `CONTROL_PLANE_AUDIENCE`

## Notes
- Control plane modules should only be enabled in `APP_MODE=control-plane`.
