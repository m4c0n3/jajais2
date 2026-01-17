# CP-0019 — Control Plane (moduly v tom istom repozitári)

## Meta
- ID: CP-0019
- Version: 1.0.0
- Title: Control Plane moduly (instances, entitlements, token issuance)
- Status: ready
- Date: 2026-01-17

## Goal
Implementovať Control Plane funkcionalitu ako moduly v tomto repozitári, aktivované cez APP_MODE=control-plane.

## Scope (MVP)
- Moduly:
  - ControlPlaneCore (API + DB)
  - ControlPlaneAdmin (Filament resources) – iba v control-plane mode
- DB entity model:
  - instances (uuid, name, api_key_hash, metadata, last_seen_at, status)
  - entitlements (instance_id, module_id, valid_to, grace_to, enabled)
  - signing_keys (kid, public_key, private_key_* , active)
- API endpoints:
  - POST /api/v1/instances/register
  - POST /api/v1/instances/heartbeat
  - POST /api/v1/licenses/refresh  -> RS256 JWT s kid + claims
- Key management:
  - `control-plane:key:rotate`
  - `control-plane:key:list`
- Feature testy pre endpoints + issuance

## DoD
- [ ] endpoints fungujú (tests)
- [ ] RS256 issuance funguje + kid
- [ ] key rotation funguje
- [ ] admin UI resources existujú
- [ ] testy prechádzajú

## Git workflow
- commit: "CP-0019: add control plane modules and token issuance"
- push
