# CP-0004 — Boot aktívnych modulov (ModuleBootManager) + enable/disable + cache

## Meta
- ID: CP-0004
- Version: 1.0.0
- Title: Boot aktívnych modulov + enable/disable + cache
- Status: ready
- Date: 2026-01-16

## Context
Máme:
- CP-0002: discovery modulov a lokálny register (tabuľka modules + module:discover)
- CP-0003: licensing cache + EnsureModuleActive middleware

Teraz potrebujeme, aby sa aplikácia pri štarte správala modulárne:
- vyhodnotila "ACTIVE" moduly (enabled + licencovanie)
- zaregistrovala ServiceProvidery aktívnych modulov
- poskytla príkazy na enable/disable a cache registry

## Scope
### In scope
- Implementovať `ModuleBootManager` (alebo `ModuleManager`) v core, ktorý:
  - načíta manifests z /modules/*/module.json
  - načíta DB stav z tabuľky modules
  - (ak modul vyžaduje licenciu) použije LicenseService na entitlement check
  - vyhodnotí ACTIVE moduly
  - zaregistruje provider triedy pre ACTIVE moduly pomocou `app()->register(...)`
- Pridať bootstrap hook, aby sa ModuleBootManager spustil pri štarte aplikácie
  - preferovane cez vlastný ServiceProvider v core
- Pridať artisan príkazy:
  - `module:enable <id>` (nastaví enabled=true)
  - `module:disable <id>` (nastaví enabled=false)
  - `module:cache` (uloží compiled registry do cache)
  - `module:clear-cache` (zmaže cache)
- Rozšíriť `module:discover`, aby voliteľne vedel doplniť provider do DB (NEukladať provider do DB; provider berieme z manifestu)
- Pridať jednoduchý "HelloWorld" modul ako reálny bootovateľný modul:
  - doplniť do `modules/HelloWorld/src/Providers/HelloWorldServiceProvider.php`
  - modul pridá jednoduchú route (napr. GET /hello) alebo aspoň registruje niečo triviálne
- Pridať testy:
  - že disabled modul sa pri boote neregistruje
  - že enabled modul s providerom sa zaregistruje
  - že licensed modul bez entitlementu sa neregistruje (na základe CP-0003 LicenseService)
- Doplniť dokumentáciu:
  - ako zapnúť modul
  - ako vytvoriť modul s providerom
  - ako cachovať registry

### Out of scope
- RBAC permissions sync (bude CP-0005)
- UI pre správu modulov (admin panel)
- Control Plane/agent/heartbeat/refresh
- Update engine

## Návrh riešenia
### Čo navrhujem
- Modulový manifest obsahuje `provider` (FQCN).
- ACTIVE rozhodnutie:
  - modul existuje v DB
  - enabled=true
  - ak license_required=true, musí byť `LicenseService->isModuleEntitled($id)` true a licencia validná/grace
  - provider trieda musí existovať (class_exists)
- Boot:
  - ak existuje cache registry, použiť cache (zníženie FS scan)
  - inak použiť discovery+DB
- Bezpečný default:
  - nový modul po discoveri je disabled, kým ho admin explicitne nepovolí

### Prečo
- Reálna modularita: modul má vlastný provider, routy, preklady, migrácie atď.
- Vďaka licenčnému checku sa licencované moduly vôbec nebootnú.
- Cache registry zabezpečí rýchly boot v produkcii.

### Alternatívy
- Bootovať všetko a blokovať iba middleware: odmietnuté (modul by mohol registrovať routy/servisy aj bez licencie).
- Ukladať provider do DB: odmietnuté (source of truth je manifest v module).

## Dopady
- Bezpečnosť: zlepší sa (neaktívne/nelicencované moduly sa vôbec neregistrujú).
- Výkon: v produkcii sa použije cache registry; bez cache ide o minimálny FS scan, ale iba pri boote.
- Kompatibilita: minimálny dopad; pozor na poradie providerov.
- Databáza: používa existujúcu tabuľku modules, nepridáva nové tabuľky.
- Prevádzka: nové príkazy pre správu modulov.

## Predpoklady a závislosti
- CP-0002 (modules tabuľka + discovery) implementované
- CP-0003 (LicenseService) implementované
- Každý modul má `module.json` s `provider` (aspoň HelloWorld)

## Úlohy pre Codex (kroky)
1) Implementuj `ModuleBootManager` (napr. `app/Support/Modules/ModuleBootManager.php`):
   - metóda `bootActiveModules()`:
     - načíta registry (z cache alebo discovery)
     - vyfiltruje ACTIVE moduly podľa pravidiel vyššie
     - registruje provider: `app()->register($providerFqcn)`
     - loguje (info) ktoré moduly boli zaregistrované a ktoré nie (dôvod)
2) Vytvor core ServiceProvider (napr. `app/Providers/ModuleSystemServiceProvider.php`):
   - v `register/boot` zavolá ModuleBootManager->bootActiveModules()
   - zaregistruje artisan commands (module:enable/disable/cache/clear-cache)
3) Zaregistruj `ModuleSystemServiceProvider` v bootstrap (Laravel 12 spôsob)
   - napr. v `bootstrap/providers.php` alebo podľa aktuálneho setupu projektu
4) Artisan príkazy:
   - `module:enable <id>`: nastav enabled=true, vypíš status
   - `module:disable <id>`: nastav enabled=false, vypíš status
   - `module:cache`: uloží compiled registry do cache (obsahuje manifest+enabled+license_required+provider)
   - `module:clear-cache`: zmaže cache key
5) Uprav/rozšír existujúci `module:discover` (ak existuje z CP-0002):
   - po synce do DB vypíše aj provider triedu z manifestu
6) Urob bootovateľný modul `HelloWorld`:
   - `modules/HelloWorld/module.json` musí obsahovať provider FQCN
   - `modules/HelloWorld/src/Providers/HelloWorldServiceProvider.php`:
     - registruje `routes/web.php` (napr. GET /hello => "Hello from module")
   - pridaj `modules/HelloWorld/routes/web.php`
7) Testy:
   - test, že pri enabled=false sa route /hello neobjaví (alebo provider nie je registrovaný)
   - test, že po enable=true sa /hello vracia 200 a očakávaný text
   - test licencovaného modulu bez entitlementu (sprav druhý test modul v tests fixtures alebo dočasne zmeň license_required v DB) => provider sa neregistruje
8) Dokumentácia:
   - pridať do README alebo docs:
     - `php artisan module:discover`
     - `php artisan module:enable HelloWorld`
     - `php artisan module:disable HelloWorld`
     - `php artisan module:cache` / `module:clear-cache`

## Akceptačné kritériá (DoD)
- [ ] Aplikácia pri boote registruje provider len pre ACTIVE moduly
- [ ] `module:enable`/`module:disable` funguje a nemení nič mimo enabled stĺpca
- [ ] `module:cache` a `module:clear-cache` funguje
- [ ] HelloWorld modul sa dá:
  - discovernúť
  - enablenúť
  - po enable vráti /hello 200
- [ ] Nelicencovaný/disabled modul sa nebootne
- [ ] Testy prechádzajú

## Validácia
- Príkazy:
  - `php artisan module:discover`
  - `php artisan module:enable HelloWorld`
  - `php artisan serve` a otestuj `GET /hello`
  - `php artisan test`
- Očakávaný výsledok:
  - /hello funguje len keď je modul active
  - testy zelené

## Rollback plán
- `git revert <commit>` (vráti modulový boot systém)
- ak vzniknú konflikty v registrácii providerov, revertni len ModuleSystemServiceProvider registráciu

## Report pre človeka
- Zmenené/pridané súbory: ModuleBootManager, ModuleSystemServiceProvider, commands, HelloWorld provider/routes, testy, docs
- Ako otestovať: Validácia príkazy

## Povinný Git workflow (platí pre tento prompt)
Po úspešnom dokončení implementácie a validácii:
- vždy vykonaj `git status`
- ak existujú zmeny, vykonaj:
  - `git add` iba relevantných súborov
  - commit message: "CP-0004: boot active modules and module commands"
  - `git push` na aktuálny branch (ak je remote nastavený)
- ak nie sú žiadne zmeny, nevytváraj prázdny commit, ale uveď to v reporte
