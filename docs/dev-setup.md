# Dev Setup

## Requirements
- PHP 8.4+
- Composer
- SQLite or MySQL
- Queue driver (database or redis)

## macOS (Herd)
1) Install Herd (includes PHP).
2) Set PHP 8.4+ in Herd.
3) Run:
   - `composer install`
   - `cp .env.example .env`
   - `php artisan key:generate`
   - `php artisan migrate`

## mise (recommended)
- Install mise: https://mise.jdx.dev
- Run:
  - `mise install`
  - `composer install`

## asdf (alternative)
- Create `.tool-versions` with:
  - `php 8.4.0`
  - `composer latest`
- Run `asdf install`.

## Docker (skeleton)
TODO: add a minimal docker-compose with app + db.

## Pre-flight check
- `bash scripts/check-env.sh`

## Tests
- `php artisan test`
