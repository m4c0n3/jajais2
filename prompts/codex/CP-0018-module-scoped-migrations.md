# CP-0018 — Module-scoped migrations (len pre enabled moduly)

## Meta
- ID: CP-0018
- Version: 1.0.0
- Title: Module-scoped migrations (enabled-only)
- Status: ready
- Date: 2026-01-17

## Goal
Migrácie modulov sa majú spúšťať iba pre enabled moduly.
Zároveň chceme, aby migrácie modulov boli uložené v module adresári.

## Scope
- Štandard cesty: `modules/<Module>/database/migrations`
- Registrácia migration paths iba pre enabled moduly:
  - cez `loadMigrationsFrom()` v module provider (bootuje len enabled modul)
  - alebo centrálny manager pre artisan migrate
- (Voliteľne) príkaz `module:migrate` (debug wrapper)
- Testy pre enabled vs disabled

## DoD
- [ ] disabled modul migrácie sa nespúšťajú
- [ ] enabled modul migrácie sa spúšťajú
- [ ] testy prechádzajú

## Git workflow
- commit: "CP-0018: add module-scoped migrations for enabled modules"
- push
