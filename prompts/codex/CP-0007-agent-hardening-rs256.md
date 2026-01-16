# CP-0007 — Agent hardening + trust model pre licencie (JWT RS256) + ops

## Meta
- ID: CP-0007
- Version: 1.0.0
- Title: Agent hardening + trust model (JWT RS256) + ops
- Status: ready
- Date: 2026-01-17

## Context
Máme Agent modul (CP-0006), ktorý posiela heartbeat a sťahuje licenčný token do `license_tokens`.
Aktuálne je parsing tokenu “lightweight” s TODO na signature verification.

Teraz potrebujeme produkčne bezpečný model:
- overiť autenticitu tokenu (signature verify)
- robustné retry/backoff + bezpečné logovanie
- lepšia observabilita a operatívne príkazy (status)
- auditovať aj zlyhania

## Scope
### In scope
- Zaviesť JWT overovanie licenčných tokenov pomocou RS256 (public key)
  - konfigurovateľný verejný kľúč:
    - `CONTROL_PLANE_JWT_PUBLIC_KEY` (PEM string) alebo `CONTROL_PLANE_JWT_PUBLIC_KEY_PATH`
  - validovať:
    - podpis (RS256)
    - `iss` (issuer) = config `CONTROL_PLANE_JWT_ISSUER`
    - `aud` (audience) = config `CONTROL_PLANE_JWT_AUDIENCE` (alebo instance_uuid)
    - exp / nbf
- Aktualizovať `LicenseService` (alebo nový `TokenVerifier`) tak, aby:
  - pri uložení tokenu (license refresh) overil token a uložil `parsed` iba ak je validný
  - invalid token sa neaktivuje (zapíše sa do DB s revoked_at alebo last_refresh_error)
- Zlepšiť Agent retry/backoff:
  - jednotné nastavenie: timeout, retries, backoff (exponential), max retry time
  - pri 5xx/429 retry; pri 4xx (okrem 429) fail fast
- Pridať `agent:status` príkaz:
  - vypíše instance_uuid, registered_at, last_heartbeat_at, last_license_refresh_at
  - vypíše license meta (valid_to/grace_to/is_valid/is_grace)
  - vypíše posledné chyby
- Bezpečné logovanie:
  - nikdy nelogovať celé tokeny ani Authorization header
  - v exception messages redaktovať secrets
- Audit log rozšírenie:
  - logovať aj FAIL udalosti:
    - agent.heartbeat_failed
    - agent.license_refresh_failed
    - license.token_invalid
- Testy:
  - unit test pre JWT verify (valid/invalid)
  - feature test, že invalid token z refresh sa neaktivuje (license gate zostane blokovať)
  - Http::fake retry path (aspoň 1 test: 429 -> retry -> success)
- Dokumentácia:
  - env/config pre public key, issuer/audience
  - postup rotácie kľúčov (krátko)

### Out of scope
- mTLS
- úplný circuit breaker (budeme mať jednoduchý backoff + počítadlo failov)
- kompletné SIEM integrácie

## Návrh riešenia
### Token trust model
- Control Plane vydáva JWT (RS256) s claims:
  - `iss`, `aud`, `exp`, `nbf` (optional), `iat`
  - `modules` (array) – povolené moduly
  - `valid_to`, `grace_to` (ISO8601 alebo epoch) – business validity
- Aplikácia:
  - overí signature + standard claims
  - následne vyhodnotí business validity (valid_to/grace_to) pre LicenseService

### Implementačný detail
- Použiť stabilnú JWT knižnicu (napr. `firebase/php-jwt`) a striktne povoliť len RS256.
- Public key sa načíta z env alebo súboru; v logs sa nikdy nevypisuje.

## Dopady
- Bezpečnosť: výrazné zlepšenie (tokeny sa nedajú “podstrčiť” bez kľúča).
- Výkon: minimálny dopad (verify len pri refresh a pri parse cache).
- Prevádzka: potreba spravovať public key a issuer/audience.

## Predpoklady a závislosti
- CP-0003 LicenseService + license_tokens existuje
- CP-0006 Agent modul existuje
- CP-0005 audit log existuje (ak nie, logovanie failov best effort)

## Úlohy pre Codex (kroky)
1) Pridaj závislosť na JWT verify (napr. `firebase/php-jwt`)
2) Pridaj config:
   - `config/control_plane.php` alebo modulový config Agent:
     - jwt_public_key / jwt_public_key_path
     - jwt_issuer, jwt_audience
     - timeouts/retries/backoff
3) Implementuj `JwtTokenVerifier` (napr. `app/Support/Licensing/JwtTokenVerifier.php`):
   - `verify(string $jwt): array` (vráti claims)
   - povoliť len RS256
4) Uprav Agent license refresh flow:
   - po stiahnutí tokenu: verify
   - ak valid -> uložiť do license_tokens s parsed claims
   - ak invalid -> uložiť záznam s last_refresh_error a audit `license.token_invalid`, NEprepísať aktívny token
5) Uprav `LicenseService`:
   - používa parsed claims a valid_to/grace_to ako doteraz
   - ak parsed chýba alebo token invalid, `isModuleEntitled` = false
6) Retry/backoff:
   - centralizuj v ControlPlaneClient
   - implementuj retry pre 429 a 5xx
7) Pridaj `agent:status` command
8) Audit log:
   - úspech aj fail eventy (minimálne pre heartbeat a refresh)
9) Testy:
   - unit verify valid/invalid (môže použiť test keypair fixture)
   - feature: invalid refresh neaktivuje licenciu
   - retry test (Http::fake sequence)
10) Dokumentácia:
   - `docs/agent-hardening.md` alebo doplnenie do Agent docs

## Akceptačné kritériá (DoD)
- [ ] JWT RS256 verify funguje (testy)
- [ ] invalid token sa neaktivuje a je auditovaný
- [ ] retry/backoff funguje aspoň pre 429/5xx (testované)
- [ ] `agent:status` existuje a vypíše relevantné info
- [ ] secrets sa nelogujú
- [ ] testy prechádzajú

## Validácia
- `php artisan test`
- `php artisan agent:status` (ak je modul enabled)
- (voliteľne) `php artisan agent:license-refresh` s fake tokenom -> očakávané fail

## Rollback plán
- `git revert <commit>`
- odstrániť JWT dependency a vrátiť sa k lightweight parsing

## Povinný Git workflow
Po úspešnom dokončení:
- `git status`
- commit: "CP-0007: agent hardening and JWT RS256 token verification"
- `git push`
