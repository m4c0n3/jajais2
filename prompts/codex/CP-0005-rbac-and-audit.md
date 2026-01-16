# CP-0005 — RBAC (Spatie) sync z manifestov + audit log minimum

## Meta
- ID: CP-0005
- Version: 1.0.0
- Title: RBAC sync z manifestov + audit log minimum
- Status: ready
- Date: 2026-01-16

## Context
Máme modulový systém (discovery, boot aktívnych modulov, licenčný gate). Teraz potrebujeme:
- jednotné riadenie prístupov (RBAC) naprieč modulmi
- minimálny audit log (kto/čo/kedy) pre citlivé operácie a admin zásahy

RBAC bude zdrojovo vychádzať z modulových manifestov `module.json`, ktoré definujú `permissions`.

## Scope
### In scope
- Nainštalovať a nakonfigurovať `spatie/laravel-permission`
- Zaviesť konvenciu permissions:
  - `<module>.<resource>.<action>` alebo `<module>.<action>`
- Implementovať `rbac:sync` artisan príkaz:
  - načíta ACTIVE moduly (rovnaké pravidlá ako boot)
  - z ich manifestov zoberie `permissions`
  - zosynchronizuje do DB:
    - vytvorí chýbajúce permissions
    - (voliteľné) označí nepoužívané ako deprecated (NEmazať automaticky)
  - zabezpečí idempotentnosť
- Zaviesť minimálne roly:
  - `super-admin` (má všetko; použije spatie "Gate::before" alebo priradenie všetkých perms)
  - `admin` (default bez všetkého, perms sa prideľujú)
- Implementovať audit log minimum:
  - DB tabuľka `audit_logs`
  - `AuditService` na zapisovanie audit udalostí
  - middleware alebo event listener pre logovanie:
    - prihlásenie (login) - voliteľné (ak už je auth)
    - `module:enable` / `module:disable`
    - `license:install`
    - `rbac:sync`
- Pridať základné testy:
  - `rbac:sync` vytvorí permissions z manifestu
  - idempotentnosť (opakované spustenie nemení nič zásadné)
  - audit log sa zapíše pri spustení vybraných commandov (aspoň 1 test)

### Out of scope
- Admin UI pre role/permissions
- ABAC (policy podľa vlastníctva objektu)
- Detailné auditovanie CRUD pre všetky entity
- Integrácia na externý SIEM

## Návrh riešenia
### Čo navrhujem
RBAC:
- použijeme Spatie package ako štandard
- `rbac:sync` bude jediný “oficiálny” spôsob ako z manifestov dostať permissions do DB
- ACTIVE moduly = iba tie, ktoré sú enabled + licencované (ak treba) + provider existuje

Audit log:
- jednoduchá tabuľka s JSON payload:
  - actor (user_id alebo "system")
  - action (string)
  - target (napr. module_id)
  - metadata (json)
  - ip, user_agent (ak dostupné)
- pre artisan príkazy logujeme aspoň: kto spustil (ak je to možné), inak "system"

### Prečo
- permissions sú súčasťou modulu → modul je prenositeľný
- sync centralizuje pravdu a minimalizuje manuálne chyby
- audit log je základ pre bezpečnosť a compliance

### Alternatívy
- Vkladať permissions ručne cez seedery: odmietnuté (drift).
- Mať audit log mimo DB: zatiaľ nie, DB minimum je dostačujúce.

## Dopady
- Bezpečnosť: výrazné zlepšenie (RBAC, audit).
- Výkon: sync je artisan, audit log je ľahký insert.
- Kompatibilita: pridávame nové tabuľky.
- Databáza/Migrácie: spatie migrácie + audit_logs migrácia.
- Prevádzka: nový príkaz `rbac:sync`.

## Predpoklady a závislosti
- CP-0002 až CP-0004 implementované (manifesty + active module logic)
- Laravel auth môže, ale nemusí byť hotový; audit pri commands funguje aj bez usera

## Úlohy pre Codex (kroky)
1) Pridať `spatie/laravel-permission` (composer) a vykonať publish/migrácie podľa dokumentácie
2) Nastaviť `Role` a `Permission` modely (štandardné)
3) Implementovať `RbacSyncCommand` (`rbac:sync`):
   - načíta active modules cez ModuleBootManager/Registry
   - pre každé permission string:
     - upsert do permissions tabuľky
   - nevymazávať permissions automaticky
   - vypísať report (koľko created)
4) Vytvoriť migráciu `audit_logs`:
   - id
   - actor_type (string: user|system)
   - actor_id (nullable bigint)
   - action (string)
   - target_type (nullable string)
   - target_id (nullable string)
   - metadata (json, nullable)
   - ip (nullable string)
   - user_agent (nullable string)
   - created_at
5) Implementovať `AuditService`:
   - `log(string $action, array $context = [])`
   - automaticky doplní actor (ak je prihlásený user)
6) Zalogovať minimálne tieto akcie:
   - pri `module:enable` / `module:disable`
   - pri `license:install`
   - pri `rbac:sync`
7) Pridať testy:
   - `rbac:sync` vytvorí permission z manifestu HelloWorld (dočasne doplň permission do manifestu)
   - audit log insert pri `rbac:sync` alebo `module:enable`
8) Dokumentácia:
   - ako pridať permissions do module.json
   - ako spustiť `php artisan rbac:sync`
   - poznámka o `super-admin`

## Akceptačné kritériá (DoD)
- [ ] Spatie permissions sú nainštalované, migrácie prebehnú
- [ ] `php artisan rbac:sync` vytvorí permissions z ACTIVE modulov
- [ ] Opakované spustenie `rbac:sync` je idempotentné
- [ ] `super-admin` rola existuje (alebo je zdokumentovaná stratégia)
- [ ] Audit log sa zapisuje minimálne pri `rbac:sync` a `module:enable/disable`
- [ ] Testy prechádzajú

## Validácia
- Príkazy:
  - `php artisan migrate`
  - `php artisan module:discover`
  - `php artisan module:enable HelloWorld`
  - `php artisan rbac:sync`
  - `php artisan test`
- Očakávaný výsledok:
  - permissions sú v DB
  - audit_logs obsahuje záznamy pre sync/enable

## Rollback plán
- `php artisan migrate:rollback`
- `git revert <commit>`

## Report pre človeka
- Zmenené/pridané súbory: composer.json/lock, config permission, migrácie, command, AuditService, testy, docs
- Ako otestovať: Validácia príkazy

## Povinný Git workflow (platí pre tento prompt)
Po úspešnom dokončení implementácie a validácii:
- vždy vykonaj `git status`
- ak existujú zmeny, vykonaj:
  - `git add` iba relevantných súborov
  - commit message: "CP-0005: RBAC sync and audit log minimum"
  - `git push` na aktuálny branch (ak je remote nastavený)
- ak nie sú žiadne zmeny, nevytváraj prázdny commit, ale uveď to v reporte
