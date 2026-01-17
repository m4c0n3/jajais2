# CP-0011 — CI pipeline (GitHub Actions) pre Laravel (PHP 8.4+)

## Meta
- ID: CP-0011
- Version: 1.0.0
- Title: CI pipeline (GitHub Actions) pre Laravel (PHP 8.4+)
- Status: ready
- Date: 2026-01-17

## Context
Po CP-0010 máme jasné požiadavky na prostredie. Teraz chceme CI, aby sa:
- testy spúšťali automaticky na push/PR
- zachytili sa problémy s dependenciami, migráciami a kompatibilitou
- (voliteľne) bezpečnostný audit dependencií

## Scope
### In scope
- GitHub Actions workflow `.github/workflows/ci.yml`
- Trigger:
  - push na main
  - pull_request
- Jobs:
  1) tests-sqlite (default)
     - PHP 8.4
     - SQLite
     - `composer install`
     - `php artisan key:generate`
     - `php artisan migrate --force`
     - `php artisan test`
  2) (voliteľné) tests-mysql
     - MySQL service
     - DB config cez env
     - migrate + test
- Cache:
  - composer cache + vendor
- Quality gates (voliteľné, ak sú v projekte):
  - `composer audit` (nezastaví build pri false positive? rozhodnúť: fail on vulnerabilities)
  - Laravel Pint (ak existuje): `./vendor/bin/pint --test`

### Out of scope
- Deployment pipeline
- Full SAST/DAST

## DoD
- [ ] CI beží na push/PR
- [ ] tests-sqlite job je zelený
- [ ] (voliteľne) mysql job je zelený
- [ ] cache funguje
- [ ] README doplnené o badge/CI info (voliteľné)

## Validácia
- otvoriť PR a pozrieť status
- lokálne nie je potrebné

## Git workflow
- commit: "CP-0011: add GitHub Actions CI"
- push
