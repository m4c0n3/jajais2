# CP-0024 — Backup/Restore + key compromise playbook (Cloudron-friendly)

## Meta
- ID: CP-0024
- Version: 1.0.0
- Title: Backup/Restore + key compromise playbook
- Status: ready
- Date: 2026-01-17

## Context
Pre produkciu potrebujeme DR plán:
- ako obnoviť DB a storage
- ako exportovať/importovať Control Plane dáta (entitlements, instances)
- čo robiť pri kompromitácii signing key alebo instance api key

## Scope
### In scope
- Dokument: `docs/backup-restore.md`
  - DB backup/restore (Cloudron backups + manuálny export)
  - storage backup (localstorage)
  - test obnovy (smoke test po restore)
- Dokument: `docs/key-compromise-playbook.md`
  - signing key compromise (rotate kid, revoke old, force refresh tokens)
  - instance api key compromise (revoke/regenerate, audit, notify)
  - postup komunikácie (webhook eventy + admin akcie)
- Pridať admin akcie (ak chýbajú):
  - rotate signing key (už možno existuje) + revoke old
  - revoke instance api key + regenerate
- Testy (aspoň pre key rotation/revoke command path)

### Out of scope
- automatizované offsite backups mimo Cloudron

## DoD
- [ ] docs/backup-restore.md existuje
- [ ] docs/key-compromise-playbook.md existuje
- [ ] kľúčové admin/CLI akcie existujú (rotate/revoke/regenerate)
- [ ] testy prechádzajú

## Git workflow
- commit: "CP-0024: add backup/restore and key compromise playbook"
- push
