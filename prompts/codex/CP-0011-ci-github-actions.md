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
- Trigger: push na main, pull_request
- Joby:
  1) `tests-sqlite` (default)
     - PHP 8.4
     - SQLite
     - composer install
     - app key generate
     - migrate --force
     - tests
  2) `tests-mysql` (voliteľné)
     - MySQL service container
     - migrate + tests
- Cache:
  - composer cache

### Out of scope
- Deployment pipeline

## DoD
- [ ] CI beží na push/PR
- [ ] tests-sqlite job je zelený
- [ ] (voliteľne) mysql job je zelený

## Validácia
- cez GitHub PR status

## Git workflow
- commit: "CP-0011: add GitHub Actions CI"
- push
