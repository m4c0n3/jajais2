# CP-0025 — Admin access bootstrap + správa používateľov (Filament)

## Meta
- ID: CP-0025
- Version: 1.0.0
- Title: Admin access bootstrap + správa používateľov
- Status: ready
- Date: 2026-01-17

## Context
Pri prihlasovaní do Filament admin panelu sa ukázalo, že problém nebol v hesle (autentifikácia OK),
ale v autorizácii: User model má `canAccessPanel()` a vyžaduje rolu `super-admin` alebo permission `admin.access`.
Ak účet nemá ani jedno, Filament odmietne prístup, čo pôsobí ako "zlé credentials".

Zároveň potrebujeme v administrácii pridať správu používateľov.

## Goals
1) Zabezpečiť, že po inštalácii systému existuje minimálne jeden admin účet s prístupom do panelu.
2) Pridať UI pre správu používateľov (CRUD) a priraďovanie rolí/oprávnení.
3) Zlepšiť UX pri odmietnu prístupu (jasná hláška/log), aby to nevyzera3) Zlepšiť UX pri odmietnu prístupu (jasná hláška/log), aby ístupu)
- Zabezpečiť existenciu:
  - role `super-admin`
  - permission `admin.access`
- Zabezpečiť, že pri inštalácii (CP-0017 `system:install` / `/install`) sa:
  - vytvorí admin účet (ak ešte neexistuje)
  - priradí sa mu rola `super-admin` (alebo minimálne permission `admin.access`)
- Pridať artisan príkaz:
  - `admin:grant {email} {--role=super-admin} {--permission=admin.access}`
- Dokumentácia:
  - `docs/admin-access.md`

### B) Správa používateľov v administrácii (Filament)
- Pridať Filament Resources (panel `admin`):
  - UsersResource (list/create/edit)
  - (voliteľne) RolesResource, PermissionsResource
- UsersResource musí umožniť:
  - zmeniť meno/email
  - reset hesla (nastaviť nové)
  - priraďovať role (multi-select)
  - priraďovať permissions (voliteľne)
  - zobrazovať stav: má admin access? (computed: super-admin alebo admin.access)
- Prístup:
  - len pre `super-admin` (alebo users.manage permission)
- RBAC:
  - "core" permissions:
    - `admin.access`
    - `users.manage`

### C) UX pri odmietnutí prístupu
- Pri odmietnutí prístupu (canAccessPanel=false):
  - zrozumiteľná správa pre používateľa
  - audit/log záznam bez sekrétov

## DoD
- [ ] Po `system:install` existuje admin s prístupom do /admin
- [ ] `admin:grant` funguje
- [ ] UsersResource existuje + priraďovanie rolí
- [ ] `admin.access` a `users.manage` existujú v DB
- [ ] Testy prechádzajú: `php artisan test`
- [ ] Docs: `docs/admin-access.md`

## Validácia
- `php artisan test`
- manuálne: login do `/admin`, správa userov

## Git workflow
- commit: "CP-0025: admin access bootstrap and user management"
- push
