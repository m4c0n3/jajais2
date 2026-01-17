# Updates (MVP)

## Overview
- Updates are delivered via a signed manifest (JWT, RS256).
- Channel selection: `stable` (default) or `beta`.
- `update:check` fetches the manifest, verifies it, and stores pending updates.
- `update:apply --id=...` applies a pending update.

## Configuration
Set in `.env` (or production env):
- `UPDATES_CHANNEL` (stable|beta)
- `UPDATES_MANIFEST_URL` or `UPDATES_MANIFEST_PATH`
- `UPDATES_JWT_PUBLIC_KEY` or `UPDATES_JWT_PUBLIC_KEY_PATH`
- `UPDATES_JWT_ISSUER`
- `UPDATES_JWT_AUDIENCE`

## Manifest format (JWT claims)
Example claims payload:
```json
{
  "iss": "control-plane",
  "aud": "jajais",
  "exp": 1924992000,
  "channel": "stable",
  "updates": [
    {
      "id": "module-helloworld-1.1.0",
      "type": "module",
      "module_id": "HelloWorld",
      "version": "1.1.0",
      "channel": "stable",
      "download_url": "https://updates.example/helloworld-1.1.0.zip"
    }
  ]
}
```

## Commands
- `php artisan update:check`
- `php artisan update:apply --id=<update_id>`

## Webhook events
- `update.available`
- `update.applied`
- `update.failed`

## Notes
- MVP applies module updates by extracting a zip to `storage/app/modules/<ModuleId>`.
- Core updates are recorded as applied but do not modify vendor or composer.
