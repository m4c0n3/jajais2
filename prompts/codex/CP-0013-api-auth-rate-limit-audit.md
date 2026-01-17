# CP-0013 — API Auth + RBAC + Rate limiting + Audit

## Meta
- ID: CP-0013
- Version: 1.0.0
- Title: API Auth + RBAC + Rate limiting + Audit
- Status: ready
- Date: 2026-01-17

## Context
Potrebujeme stabilný integračný povrch: API pre moduly, n8n a externé systémy.
Už máme RBAC + audit. Teraz doplníme:
- API autentifikáciu
- rate limiting
- auditovanie API volaní
- základný kontrakt/dokumentáciu

## Scope
### In scope
- Auth: prefer Laravel Sanctum (API tokeny)
- Middleware: auth, RBAC permission gates, rate limiting
- Audit pre citlivé endpointy
- Základné endpointy:
  - GET /api/v1/system/status (chránené)
  - GET /api/v1/webhooks/endpoints (chránené, read-only)
- Docs: docs/api.md

## DoD
- [ ] API auth funguje
- [ ] rate limiting funguje
- [ ] audit loguje citlivé endpointy
- [ ] testy prechádzajú

## Git workflow
- commit: "CP-0013: API auth, RBAC, rate limiting and audit"
- push
