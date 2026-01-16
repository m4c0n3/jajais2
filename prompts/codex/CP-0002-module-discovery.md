# CP-0002 — Module discovery + modules tabuľka + artisan module:discover

## Meta
- ID: CP-0002
- Version: 1.0.0
- Title: Module discovery + modules tabuľka + artisan module:discover
- Status: ready
- Date: 2026-01-16

## Context
Projekt používa modulárnu štruktúru, kde každý modul je self-contained v /modules/<ModuleName>. Potrebujeme základný mechanizmus na:
- discovery modulov cez module.json
- synchronizáciu do DB tabuľky modules
- minimálny ModuleRegistry/ModuleManager v core
- artisan príkaz na spustenie discovery

Tento krok ešte nerieši licencovanie, RBAC sync, ani runtime gating. To príde v ďalších CP.

## Scope
### In scope
- Zaviesť directory convention: `/modules/*/module.json`
- Vytvoriť DB tabuľku `modules` (lokálny stav modulov)
- Implementovať `ModuleManifest` (DTO) + `ModuleDiscovery` (scan FS) + `ModuleRepository` (DB sync)
- Implementovať `ModuleRegistry` (načítanie manifestov + DB stav)
- Pridať artisan príkaz `module:discover`:
  - načíta manifests
  - syncne tabuľku `modules`
  - vypíše prehľad (id, version, enabled, license_required)
- Pridať minimálnu konfiguráciu `config/modules.php` (napr. base_path, cache key)
- Základné testy (aspoň 1–2) pre discovery + sync (Feature/Unit)

### Out of scope
- Enable/disable UI, runtime bootovanie providerov z modulov (to bude CP-0003/CP-0004)
- Licencie (entitlements), control plane, agent, heartbeat
- RBAC/permissions sync
- Vite/Inertia modulový frontend

## Návrh riešenia
### Čo navrhujem
- Modul je identifikovaný manifestom `module.json` v koreňi modulu.
- Discovery prejde `modules/*/module.json`, načíta JSON a vytvorí `ModuleManifest` objekty.
- Sync do DB:
  - ak modul v DB neexistuje, vytvorí sa s `enabled=false` (bezpečný default)
  - ak existuje, aktualizuje sa name/version/requires_core/license_required
  - `enabled` sa nemení (neprepisovať administrátorské rozhodnutie)
- `module:discover` je idempotentný a bezpečný.

### Prečo
- Získaš jednotný register modulov a ich metadát.
- Pripraví to pôdu pre licencovanie a runtime gating bez chaosu.

### Alternatívy
- Použiť externý balík na moduly: odmietnuté ako základ (chceme vlastný boot flow a kontrolu).

## Dopady
- Bezpečnosť: novoinštalované moduly sú disabled defaultne.
- Výkon: discovery sa spúšťa cez artisan (nie na každom requeste).
- Kompatibilita: nezasahuje do existujúcich rout/app.
- Databáza/Migrácie: pridáva migráciu pre tabuľku modules.
- Prevádzka/DevOps: nový artisan príkaz pre CI alebo deploy hook.

## Predpoklady a závislosti
- Laravel 12 + PHP 8.3 (predpoklad projektu)
- Prístup k FS priečinku /modules
- Databázové pripojenie pripravené pre migrácie

## Úlohy pre Codex (kroky)
1) Vytvor migráciu pre tabuľku `modules` s poliami:
   - id (string, PK)
   - name (string)
   - enabled (boolean, default false)
   - installed_version (string, nullable alebo default z manifestu)
   - requires_core (string, nullable)
   - license_required (boolean, default false)
   - last_booted_at (nullable datetime)
   - last_boot_status (string/enum, nullable)
   - last_boot_error (text, nullable)
   - timestamps
2) Vytvor core triedy (napr. v `app/Modules/Core/...` alebo `app/Support/Modules/...`):
   - `ModuleManifest` (id, name, version, provider, requires_core, license_required, permissions, routes, healthchecks)
   - `ModuleDiscovery` (scan + parse JSON + validácia povinných polí)
   - `ModuleRepository` (DB upsert bez prepisu enabled)
   - `ModuleRegistry` (vráti kolekciu modulov: manifest + DB state)
3) Vytvor config `config/modules.php`:
   - `path` => base_path('modules')
   - `cache_key` => 'modules.registry'
4) Pridaj artisan command `module:discover`:
   - použije ModuleDiscovery + ModuleRepository
   - vypíše tabuľku/zoznam
   - return code != 0 pri fatálnej chybe (napr. invalid JSON)
5) Pridaj minimálny ukážkový modul (len na testovanie discovery), napr.:
   - `modules/HelloWorld/module.json` (bez provider registrácie zatiaľ)
6) Pridaj testy:
   - unit test pre parse manifestu
   - feature test pre upsert do DB (enabled sa neprepíše)
7) Aktualizuj dokumentáciu:
   - krátka sekcia do docs alebo README: ako pridať modul + spustiť `php artisan module:discover`

## Akceptačné kritériá (DoD)
- [ ] `php artisan module:discover` nájde modul `HelloWorld` z `/modules/HelloWorld/module.json`
- [ ] V DB sa vytvorí záznam v `modules` pre HelloWorld s `enabled=false` default.
- [ ] Opakované spustenie `module:discover` je idempotentné.
- [ ] `enabled` sa pri opakovanom sync nikdy neprepíše.
- [ ] Testy prechádzajú.

## Validácia
- Príkazy:
  - `php artisan migrate`
  - `php artisan module:discover`
  - `php artisan test`
- Očakávaný výsledok:
  - migrácie prebehnú
  - command vypíše zoznam modulov vrátane HelloWorld
  - testy sú zelené

## Rollback plán
- `php artisan migrate:rollback` (ak bola posledná migrácia modules)
- `git revert <commit>` alebo odstrániť pridané triedy/súbory

## Report pre človeka
- Zmenené/pridané súbory: migrácia, config/modules.php, triedy Module*, artisan command, modules/HelloWorld, testy, docs/README update
- Ako otestovať: spusti Validácia príkazy
