# CP-0015 — Automatické aktualizácie jadra a modulov (signed manifests + rollback)

## Meta
- ID: CP-0015
- Version: 1.0.0
- Title: Automatické aktualizácie jadra a modulov
- Status: ready
- Date: 2026-01-17

## Context
Požiadavka: automatické aktualizácie systému a modulov.
Máme licencie a trust model → vieme bezpečne distribuovať update manifesty.

## Scope
### In scope (MVP)
- signed update manifest (RS256/Ed25519)
- update channel (stable/beta)
- CLI: update:check, update:apply
- verify signature, download, apply (MVP)
- audit + webhooks: update.available/applied/failed
- docs/updates.md

## DoD
- [ ] signed manifest verify funguje
- [ ] update:check + update:apply fungujú v MVP
- [ ] audit + webhook udalosti
- [ ] testy prechádzajú

## Git workflow
- commit: "CP-0015: auto-update MVP for core and modules"
- push
