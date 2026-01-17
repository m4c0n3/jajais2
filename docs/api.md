# API (v1)

## Authentication
- Uses Laravel Sanctum personal access tokens.
- Create a token via the app shell or a seed/CLI flow.
- Send it as `Authorization: Bearer <token>`.

## Rate limiting
- Default: 60 requests/minute per token (fallback to IP).

## Endpoints

### GET /api/v1/system/status
- Auth: `auth:sanctum` + permission `system.status.view`
- Returns system status summary.

### GET /api/v1/webhooks/endpoints
- Auth: `auth:sanctum` + permission `webhooks.view`
- Returns read-only webhook endpoints (no secrets).

## Audit
- API calls are logged in `audit_logs` (best-effort) for sensitive endpoints.
