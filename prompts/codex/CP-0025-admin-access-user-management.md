# CP-0025 — Admin access bootstrap + správa používateľov (Filament)

## Meta
- ID: CP-0025
- Version: 1.0.0
- Title: Admin access bootstrap + správa používateľov
- Status: ready
- Date: 2026-01-18

## Context
Pri prihlásení do Filament admin panelu sa ukázalo, že problém nebol v hesle (autentifikácia OK),
ale v autorizácii: User model má `canAccessPanel()`, ktorá vyžaduje rolu `super-admin` alebo permission `admin.access`.
Ak účet nemá ani jedno, Filament odmietne prístup. UX môže pôsobiť ako zlé prihlasovacie údaje.

Zároveň potrebujeme do administrácie doplniť správu používateľov.

## Goals
1) Zabezpečiť, že po inštalácii existuje admin účet s prístupom do /admin.
2) Pridať UI pre správu používateľov (CRUD) a priraďovanie rolí/oprávnení.
3) Zlepšiť UX pri odmietnutí prístupu (jasná správa), aby to nevyzeralo ako 3) Zlepšiť UX pri odmietnutí prístupu (jasná správa), aby to existenciu:
  - role `super-admin`
  - permissions: `admin.access`, `users.manage`
- Upraviť installer (CP-0017 /install a/alebo system:install), aby:
  - vytvoril/našiel "primárneho admina"
  - priradil mu rolu `super-admin` (alebo min. permission `admin.access`)
- Pridať artisan príkaz:
  - `admin:grant {email} {--role=super-admin} {--permission=admin.access}`
  - musí byť idempotentný (opakované spustenie OK)
- Docs:
  - `docs/admin-access.md` (ako riešiť "neviem sa dostať do adminu")

### B) Správa používateľov v admin paneli (Filament)
- Pridať Filament resource:
  - UsersResource (list/create/edit/view)
- UsersResource musí umožniť:
  - upraviť meno/email
  - nastaviť/reset heslo (pri editácii voliteľné)
  - priraďovať role (multi-select)
  - (voliteľne) priraďovať permissions (ak je to rýchle a čisté)
  - zobraziť stĺpec "Admin access" (computed: super-admin alebo admin.access)
- Prístup k správe používateľov:
  - len super-admin alebo permission `users.manage`

### C) UX fix (autorizácia po úspešnom hesle)
- Zabezpečiť jasnú hlášku:
  - ak email+heslo sú správne, ale používateľ nemá admin access, zobraz:
    "Účet je platný, ale nemáte oprávnenie pre administráciu."
- Implementačne preferovať vlastnú Filament Login page (override authenticate),
  aby bolo možné rozlíšiť "zlé heslo" vs "nemáš právo".

### D) Build artefakty
- Ak `composer dump-autoload` / Filament hook vygeneruje zmeny v `public/js/filament/**` alebo `public/css/filament/**`,
  tieto zmeny NEZAHŔŇAŤ do commitu (revert) a pridať do `.gitignore`, ak sú to build artefakty.

## DoD
- [ ] Po install existuje admin s prístupom do /admin (super-admin alebo admin.access)
- [ ] `php artisan admin:grant email` funguje
- [ ] UsersResource existuje a funguje (role assignment + reset hesla)
- [ ] UX: pri "no admin access" sa nezobrazuje "bad credentials", ale jasná správa
- [ ] Testy:
  - unit/feature test pre `admin:grant`
  - unit test pre `canAccessPanel()` (super-admin/admin.access)
- [ ] `php artisan test` PASS
- [ ] Docs: `docs/admin-access.md`

## Validácia
- `php artisan optimize:clear`
- `php artisan test`
- manuálne:
  - vytvoriť usera v admin UI, priradiť rolu, login do /admin

## Git workflow
- commit: "CP-0025: admin access bootstrap and user management"
- push
