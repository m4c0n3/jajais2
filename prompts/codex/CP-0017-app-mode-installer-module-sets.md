# CP-0017 — App Mode (client/control-plane) + Installer (first-run) + Module Sets

## Meta
- ID: CP-0017
- Version: 1.0.0
- Title: App Mode + Installer + Module Sets
- Status: ready
- Date: 2026-01-17

## Goal
Používateľ pri inštalácii vyberie režim:
- CLIENT app
- CONTROL PLANE
Podľa režimu sa automaticky zapnú príslušné moduly a systém sa inicializuje.

## Scope
### In scope
- Zaviesť "mode" = `client|control-plane`:
  - env `APP_MODE` (optional, non-interactive)
  - persistovaný "mode" (DB tabuľka `system_settings` alebo `app_settings`)
  - "lock" po inicializácii (nesmie sa prepnúť bez resetu)
- First-run flow:
  - route `/install` (dostupné iba ak ešte nie je initialized)
  - výber režimu + potvrdenie
  - kroky inicializácie:
    1) uložiť mode + lock
    2) `module:discover`
    3) enable modul set podľa mode
    4) clear/cache registry (ak máte)
    5) spustiť migrácie (core + enabled modules; CP-0018 rieši detaily)
    6) vytvoriť admin user / priradiť super-admin (ak nie existuje)
    7) `rbac:sync`
- Module sets:
  - definovať v `config/module_sets.php`:
    - client: Agent, Webhooks, Admin (min), Updates
    - control-plane: ControlPlane*, Admin, Audit/RBAC
  - Installer používa sety, nie hardcoded zoznam
- CLI alternatíva:
  - `system:install --mode=client|control-plane --non-interactive`

### Out of scope
- kompletný provisioning (SMTP/LDAP/etc)
- multi-tenant

## DoD
- [ ] /install existuje a funguje iba keď nie je initialized
- [ ] mode sa uloží a zamkne
- [ ] moduly sa automaticky povolia podľa setu
- [ ] po install prechádzajú testy
- [ ] docs: `docs/installer.md`

## Git workflow
- commit: "CP-0017: add app mode installer and module sets"
- push
