# Agent Hardening (JWT RS256)

## Configuration
- `CONTROL_PLANE_JWT_PUBLIC_KEY` or `CONTROL_PLANE_JWT_PUBLIC_KEY_PATH`
- `CONTROL_PLANE_JWT_ISSUER`
- `CONTROL_PLANE_JWT_AUDIENCE` (optional; falls back to instance UUID if unset)
- `CONTROL_PLANE_RETRY`, `CONTROL_PLANE_RETRY_BACKOFF_MS`, `CONTROL_PLANE_RETRY_MAX_SECONDS`

## Token Verification
- Only RS256 is accepted.
- `iss`, `aud`, `exp`, and `nbf` are validated.
- Invalid tokens are stored as revoked and never activated.

## Key Rotation
1) Distribute the new public key to instances.
2) Rotate signing keys in Control Plane.
3) Remove the old public key after all instances have updated.
