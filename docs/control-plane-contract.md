# Control Plane API Contract (v1)

## Base
- Base URL: `CONTROL_PLANE_URL` (must be HTTPS in production)
- Auth: `Authorization: Bearer <CONTROL_PLANE_TOKEN>` (or registration token for bootstrap)
- Content-Type: `application/json`

## Endpoints

### POST /api/v1/instances/register
Bootstrap a new instance and receive an instance UUID.

Request:
```json
{
  "app_env": "production",
  "app_version": "1.0.0",
  "php_version": "8.3.2",
  "timestamp": "2026-01-16T12:00:00Z"
}
```

Response (201):
```json
{
  "instance_uuid": "0f40f7d0-4e6a-45a0-9b92-09ea2a13e86f",
  "registered_at": "2026-01-16T12:00:02Z"
}
```

### POST /api/v1/instances/{instance_uuid}/heartbeat
Send periodic heartbeat with system/module status.

Request:
```json
{
  "instance_uuid": "0f40f7d0-4e6a-45a0-9b92-09ea2a13e86f",
  "app_env": "production",
  "app_version": "1.0.0",
  "php_version": "8.3.2",
  "modules": [
    {
      "id": "hello-world",
      "version": "1.0.0",
      "enabled": true,
      "license_required": false
    }
  ],
  "license": {
    "valid_to": "2026-12-31T00:00:00Z",
    "grace_to": "2027-01-15T00:00:00Z",
    "is_valid": true,
    "is_grace": false
  },
  "timestamp": "2026-01-16T12:01:00Z"
}
```

Response (200):
```json
{
  "status": "ok"
}
```

### POST /api/v1/instances/{instance_uuid}/license/refresh
Request a refreshed entitlement token.

Request:
```json
{
  "instance_uuid": "0f40f7d0-4e6a-45a0-9b92-09ea2a13e86f",
  "timestamp": "2026-01-16T12:05:00Z"
}
```

Response (200):
```json
{
  "token": "<jwt-or-json>",
  "valid_to": "2026-12-31T00:00:00Z",
  "grace_to": "2027-01-15T00:00:00Z",
  "parsed": {
    "modules": ["hello-world"],
    "valid_to": "2026-12-31T00:00:00Z",
    "grace_to": "2027-01-15T00:00:00Z"
  }
}
```

## Error Codes
- 401 Unauthorized: missing/invalid bearer token
- 403 Forbidden: instance not allowed
- 422 Unprocessable Entity: invalid payload
- 500 Internal Server Error: server-side failure
