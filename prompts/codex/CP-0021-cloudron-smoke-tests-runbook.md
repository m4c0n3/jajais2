# CP-0021 — Cloudron smoke tests + runbook (client aj control-plane)

## Meta
- ID: CP-0021
- Version: 1.0.0
- Title: Cloudron smoke tests + runbook
- Status: ready
- Date: 2026-01-17

## Context
CP-0020 priniesol Cloudron balíček. Potrebujeme potvrdiť, že nasadenie je stabilné a mať runbook
pre základnú diagnostiku (logs, health, DB, redis, scheduler/queue).

## Scope
### In scope
- Dokument: `docs/cloudron-smoke-tests.md`
  - scenár A: inštalácia v režime CLIENT
  - scenár B: inštalácia v režime CONTROL-PLANE
  - checklist: /health, /install, login do adminu, DB migrácie, storage RW, queue, scheduler
- Dokument: `docs/cloudron-runbook.md`
  - kde nájsť logy v Cloudrone
  - ako spustiť diagnostické artisan príkazy v containeri
  - čo kontrolovať pri: 500 error, webhook failures, license refresh failures, DB issues
- Pridať minimálne 1–2 "diagnostic" artisan príkazy (ak ešte nie sú):
  - `cloudron:diag` (vypíše APP_MODE, DB driver, redis driver, storage path, last heartbeat/refresh, failed webhooks count)
- README doplniť sekciu "Cloudron: smoke test + runbook links".

### Out of scope
- plná observability stack integrácia

## DoD
- [ ] docs/cloudron-smoke-tests.md existuje a je praktický
- [ ] docs/cloudron-runbook.md existuje
- [ ] `php artisan cloudron:diag` existuje (ak chýbal) a nevyzrádza sekréty
- [ ] testy prechádzajú

## Validácia
- `php artisan test`
- `php artisan cloudron:diag`

## Git workflow
- commit: "CP-0021: cloudron smoke tests and runbook"
- push
