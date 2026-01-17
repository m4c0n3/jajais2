# CP-0022 — Cloudron release & update runbook (verzovanie, update, rollback)

## Meta
- ID: CP-0022
- Version: 1.0.0
- Title: Cloudron release & update runbook
- Status: ready
- Date: 2026-01-17

## Context
Potrebujeme jednotný proces release/update pre Cloudron (build → install/update → rollback).
Musí byť jasné kedy sa spúšťajú migrácie a ako sa postupuje pri zlyhaní.

## Scope
### In scope
- Dokument: `docs/cloudron-release.md`
  - verzovanie (git tag / release notes)
  - build balíka (cloudron build)
  - update (cloudron update)
  - rollback (cloudron restore/rollback postup)
  - migrácie: kedy sa spúšťajú (v start.sh) a ako riešiť breaking changes
- Pridať `docs/release-notes-template.md` (čo zapisovať pri každom release)
- Doplniť "release checklist" o Cloudron špecifiká (napojiť na CP-0012 docs/release-checklist.md)

### Out of scope
- automatický CD pipeline

## DoD
- [ ] docs/cloudron-release.md existuje
- [ ] release notes template existuje
- [ ] release checklist doplnený o Cloudron kroky
- [ ] bez kódu alebo iba minimálne úpravy (docs-first), testy prechádzajú

## Validácia
- `php artisan test`

## Git workflow
- commit: "CP-0022: cloudron release and update runbook"
- push
