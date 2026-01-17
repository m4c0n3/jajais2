# CP-0016 — Control Plane Server MVP (registry, entitlements, key rotation)

## Meta
- ID: CP-0016
- Version: 1.0.0
- Title: Control Plane Server MVP
- Status: ready
- Date: 2026-01-17

## Context
Požiadavka: centralizovaný riadiaci systém pre monitorovanie a platnosť licencií.
Máme Agent modul v klientoch → potrebujeme server MVP.

## Scope (blueprint)
- Prefer: samostatný Laravel projekt (separate repo)
- API endpoints:
  - POST /api/v1/instances/register
  - POST /api/v1/instances/heartbeat
  - POST /api/v1/licenses/refresh
- DB: instances, modules, entitlements, keys (kid)
- JWT issuance: RS256 tokens (iss/aud/exp/modules/valid_to/grace_to)
- Admin UI minimal (Filament) pre instances + entitlements
- docs/control-plane-mvp.md

## DoD
- [ ] blueprint prompt pripravený
- [ ] definované API a entity
- [ ] security model (RS256, key rotation) popísaný

## Git workflow
- commit: "CP-0016: control plane server MVP blueprint"
- push
