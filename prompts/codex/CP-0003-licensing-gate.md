# CP-0003 — Licensing cache + EnsureModuleActive middleware

## Meta
- ID: CP-0003
- Version: 1.0.0
- Title: Licensing cache + EnsureModuleActive middleware
- Status: ready
- Date: 2026-01-16

## Context
Máme základný module discovery a register v DB (CP-0002). Teraz potrebujeme pripraviť "licensing gate" na úrovni core, aby sa dal modul runtime-ovo blokovať podľa:
- lokálneho stavu `modules.enabled`
- platnosti licencie (entitlement token z centrálneho systému)

Tento krok ešte nerieši Control Plane ani agentické sťahovanie licencií – iba lokálny licensing cache + middleware.

## Scope
### In scope
- Vytvoriť DB tabuľku `license_tokens` (cache posledného entitlement tokenu)
- Pridať `LicenseService`:
  - načíta posledný token z DB
  - overí, či je ešte platný (valid_to / grace_to)
  - poskytne API: `isModuleEntitled($moduleId): bool`
  - vráti aj metadata (valid_to, grace_to, features/limits – voliteľne)
- Pridať artisan príkaz `license:install` (alebo `license:import`) na uloženie tokenu do DB:
  - token sa zadá cez `--token="..."` alebo `--file=path`
  - uloží sa do `license_tokens` ako "active"
- Pridať middleware `EnsureModuleActive`:
  - overí, že modul existuje v DB a je `enabled=true`
  - ak modul vyžaduje licenciu (`license_required=true` v modules tabuľke), overí entitlement cez LicenseService
  - ak nie je aktívny, vráti 403 (alebo 404 podľa konvencie) s jasnou chybou
- Pridať základné testy:
  - že `enabled` je vyžadované
  - že licencovaný modul bez entitlementu je blokovaný
  - že licencovaný modul s entitlementom prejde
- Doplniť krátku dokumentáciu: ako vložiť licenčný token a použiť middleware v routach

### Out of scope
- Integrácia na Control Plane (register/heartbeat/refresh)
- Kryptografické podpisovanie/overovanie tokenov proti verejnému kľúču (ak je to rozsiahle, nechaj ako "hook" a TODO)
- RBAC sync z permissions
- Runtime registrácia providerov modulov podľa licencie

## Návrh riešenia
### Čo navrhujem
- `license_tokens` bude obsahovať posledný token + `valid_to` a `grace_to` (datetime).
- Token claims (parsed) budú uložené do JSON stĺpca `parsed` pre rýchly prístup.
- `LicenseService` bude robiť:
  - "effective validity": ak `now <= valid_to` => valid
  - ak `valid_to < now <= grace_to` => grace režim (stále povoliť, ale logovať varovanie)
  - ak `now > grace_to` => invalid
- Entitlements budú reprezentované minimálne ako zoznam povolených modulov:
  - `parsed.modules = ["blog","shop",...]`
  - voliteľne aj `parsed.features`, `parsed.limits`

### Prečo
- Umožní to okamžite implementovať "licensing gate" bez závislosti na centrálnej infra.
- Middleware je univerzálny a použiteľný v module routes group.

### Alternatívy
- Overovať licenciu len v UI: odmietnuté (backend musí vynucovať).
- Ukladať entitlementy priamo do tabuľky modules: odmietnuté (entitlement je externý stav, nech je oddelený).

## Dopady
- Bezpečnosť:
  - modulové endpointy budú môcť byť blokované, čo je pozitívne.
  - bez kryptografického overovania tokenu je to len "mechanizmus", nie plná bezpečnosť (ak sa crypto neimplementuje v tomto kroku, musí byť jasne označené).
- Výkon: LicenseService číta posledný token z cache/DB; odporúča sa cache v pamäti.
- Kompatibilita: žiadne breaking zmeny.
- Databáza/Migrácie: nová migrácia `license_tokens`.
- Prevádzka/DevOps: nový príkaz `license:install`.

## Predpoklady a závislosti
- CP-0002 implementované (tabuľka `modules`, registry)
- Laravel migrácie funkčné
- Pre testy možnosť vložiť token do DB

## Úlohy pre Codex (kroky)
1) Vytvor migráciu pre tabuľku `license_tokens`:
   - id (PK)
   - fetched_at (datetime, nullable)
   - valid_to (datetime)
   - grace_to (datetime, nullable)
   - token (longText)
   - parsed (json, nullable)
   - revoked_at (datetime, nullable)
   - last_refresh_status (string, nullable)
   - last_refresh_error (text, nullable)
   - timestamps
2) Implementuj `LicenseService` (napr. `app/Support/Licensing/LicenseService.php`):
   - načítaj posledný token (najnovší podľa created_at alebo valid_to)
   - metódy:
     - `isLicenseValid(): bool`
     - `isInGrace(): bool`
     - `isModuleEntitled(string $moduleId): bool`
     - `getMeta(): array` (valid_to, grace_to)
   - cache-ni parsed výsledok (napr. cache key `license.active`)
3) Implementuj artisan command `license:install`:
   - `--token` alebo `--file`
   - token uložiť do DB
   - parse token claims aspoň na modulový zoznam do `parsed` (ak parse nie je možné, uložiť token a parsed nechaj null)
4) Implementuj middleware `EnsureModuleActive`:
   - parameter `module_id` (napr. `EnsureModuleActive:blog`)
   - načíta modul z DB (tabuľka modules)
   - ak `enabled=false` => 403
   - ak `license_required=true` a `LicenseService->isModuleEntitled(module_id)` je false alebo licencia invalid => 403
5) Pridaj testy:
   - Feature test pre middleware bez licencie (licensed module) => 403
   - Feature test s uloženým tokenom, ktorý obsahuje modul => 200
   - Feature test modul disabled => 403
6) Dokumentácia:
   - ako uložiť token: `php artisan license:install --token="..."`
   - ako použiť middleware v routes group

## Akceptačné kritériá (DoD)
- [ ] Migrácia `license_tokens` existuje a prebehne
- [ ] `php artisan license:install --token="..."` uloží token do DB
- [ ] Middleware `EnsureModuleActive` blokuje:
  - modul disabled
  - licencovaný modul bez entitlementu alebo po grace
- [ ] Middleware pustí request pre licencovaný modul s entitlementom
- [ ] Testy prechádzajú

## Validácia
- Príkazy:
  - `php artisan migrate`
  - `php artisan license:install --token="dummy"`
  - `php artisan test`
- Očakávaný výsledok:
  - migrácie ok
  - command vytvorí záznam v license_tokens
  - testy zelené

## Rollback plán
- `php artisan migrate:rollback` (posledná migrácia)
- `git revert <commit>` pre návrat k predošlému stavu

## Report pre človeka
- Zmenené/pridané súbory: migrácia license_tokens, LicenseService, command, middleware, testy, docs
- Ako otestovať: spusti Validácia príkazy

## Povinný Git workflow (platí pre tento prompt)
Po úspešnom dokončení implementácie a validácii:
- vždy vykonaj `git status`
- ak existujú zmeny, vykonaj:
  - `git add` iba relevantných súborov
  - commit message: "CP-0003: licensing cache and module active middleware"
  - `git push` na aktuálny branch (ak je remote nastavený)
- ak nie sú žiadne zmeny, nevytváraj prázdny commit, ale uveď to v reporte
