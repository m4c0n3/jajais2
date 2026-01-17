# Control Plane Server MVP (Blueprint)

## Scope
This document describes a minimal Control Plane server for managing instance registration, heartbeat, and license refresh for the Agent module.

## Entity Model

### instances
- `id` (pk)
- `instance_uuid` (uuid, unique)
- `name` (string, optional)
- `status` (active|suspended)
- `registered_at` (datetime)
- `last_heartbeat_at` (datetime)
- `last_license_refresh_at` (datetime)
- `last_ip` (string)
- `metadata` (json)
- `created_at`, `updated_at`

### modules
- `id` (pk, string)
- `name` (string)
- `version` (string)
- `license_required` (bool)
- `created_at`, `updated_at`

### entitlements
- `id` (pk)
- `instance_uuid` (uuid)
- `module_id` (string)
- `valid_to` (datetime)
- `grace_to` (datetime)
- `created_at`, `updated_at`

### keys
- `id` (pk)
- `kid` (string, unique)
- `public_key` (text)
- `private_key` (text, encrypted at rest)
- `active` (bool)
- `not_before` (datetime)
- `expires_at` (datetime)
- `created_at`, `updated_at`

## API Contract (v1)

### POST /api/v1/instances/register
Registers a new instance and returns an instance secret.

**Request**
```json
{
  "instance_uuid": "<uuid>",
  "name": "My Instance",
  "metadata": {"region": "eu"}
}
```

**Response**
```json
{
  "instance_uuid": "<uuid>",
  "instance_secret": "<opaque_secret>",
  "registered_at": "2026-01-17T12:34:56Z"
}
```

### POST /api/v1/instances/heartbeat
**Auth**: HMAC or shared secret header

**Request**
```json
{
  "instance_uuid": "<uuid>",
  "status": {"version": "1.0.0", "modules": ["HelloWorld"]}
}
```

**Response**
```json
{
  "ok": true,
  "server_time": "2026-01-17T12:35:10Z"
}
```

### POST /api/v1/licenses/refresh
**Auth**: HMAC or shared secret header

**Request**
```json
{
  "instance_uuid": "<uuid>",
  "module_ids": ["HelloWorld"]
}
```

**Response**
```json
{
  "token": "<JWT RS256>",
  "valid_to": "2026-02-01T00:00:00Z",
  "grace_to": "2026-02-15T00:00:00Z"
}
```

## JWT Issuance
- Algorithm: RS256
- Claims: `iss`, `aud`, `exp`, `nbf`, `modules`, `valid_to`, `grace_to`
- Header: `kid` for key rotation
- Rotation: keep old keys for overlap, remove after clients update.

## Security Notes
- Store instance secrets hashed or encrypted.
- Rate-limit register/heartbeat/refresh endpoints.
- Audit all token issuance and entitlement changes.
- Protect private keys with encryption and strict access controls.
