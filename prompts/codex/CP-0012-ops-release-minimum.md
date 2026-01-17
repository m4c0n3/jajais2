# CP-0012 — Ops/Release minimum (health, system status, release checklist)

## Meta
- ID: CP-0012
- Version: 1.0.0
- Title: Ops/Release minimum
- Status: ready
- Date: 2026-01-17

## Context
Máme moduly, licencie, agent, RBAC, webhooks a admin UI. Potrebujeme minimálny ops základ:
- healthcheck
- system status (DB/queue/licence/DLQ)
- release checklist a produkčné .env šablóny

## Scope
### In scope
- Endpoint `/health` (200 OK) + voliteľné `/health/details` (chránené)
- `system:status` command:
  - DB connectivity
  - queue driver + základný smoke
  - posledný heartbeat a license refresh
  - webhook DLQ (failed deliveries count)
  - licencia (valid/grace) summary
- `.env.production.example`
- `docs/release-checklist.md` (migrate, optimize, queue worker, scheduler)
- Audit log pre kritické ops akcie (best effort)

### Out of scope
- Full observability stack
- Deployment automation

## DoD
- [ ] /health existuje
- [ ] system:status existuje a dáva zrozumiteľný výstup
- [ ] docs/release-checklist.md existuje
- [ ] .env.production.example existuje
- [ ] testy prechádzajú

## Git workflow
- commit: "CP-0012: ops and release minimum"
- push
