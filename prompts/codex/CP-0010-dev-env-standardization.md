# CP-0010 — Dev environment standardization (PHP 8.4+) + pre-flight checks

## Meta
- ID: CP-0010
- Version: 1.0.0
- Title: Dev environment standardization (PHP 8.4+) + pre-flight checks
- Status: ready
- Date: 2026-01-17

## Context
Projekt po CP-0009 vyžaduje PHP >= 8.4 (Filament). Chceme odstrániť "it works on my machine" a mať
jednoznačný setup postup + automatickú kontrolu prostredia.

## Scope
### In scope
- Explicitne zdokumentovať požiadavky:
  - PHP >= 8.4
  - Composer
  - DB driver (SQLite/MySQL)
  - Queue driver (database/redis)
- Pridať version pin súbor:
  - preferovaný: `.mise.toml` (php, composer)
  - alternatíva (poznámka v docs): asdf `.tool-versions`
- Pridať `scripts/check-env.sh`:
  - kontrola `php -v` (min 8.4)
  - kontrola `composer -V`
  - kontrola základných PHP extensions
  - kontrola zápisu do storage/ a bootstrap/cache
  - odporučené kroky na opravu
- Pridať `docs/dev-setup.md`:
  - macOS (Herd) + CLI
  - Linux (apt/dnf) alebo mise/asdf
  - Docker (voliteľné) — len skeleton (docker-compose pre php+db)
- Aktualizovať README:
  - Quickstart
  - Requirements (PHP 8.4+)
  - ako spustiť pre-flight + tests

### Out of scope
- Kompletné CI pipeline (to bude samostatný CP)
- Full production deployment guide

## DoD
- [ ] `docs/dev-setup.md` existuje
- [ ] `scripts/check-env.sh` existuje a beží
- [ ] README obsahuje requirements + quickstart
- [ ] Repo obsahuje version pin súbor (`.mise.toml`)
- [ ] Testy stále prechádzajú

## Validácia
- `bash scripts/check-env.sh`
- `composer install`
- `php artisan test`

## Git workflow
- commit: "CP-0010: standardize dev environment and add pre-flight checks"
- push
