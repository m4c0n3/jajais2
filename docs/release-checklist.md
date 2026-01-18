# Release Checklist

## Pre-release
- Verify environment variables in `.env.production` (no secrets in repo).
- Run `composer install --no-dev --optimize-autoloader`.
- Run `php artisan migrate --force`.
- Run `php artisan test` (optional for hotfix).
- Cloudron: build and stage the image (`cloudron build` or `docker build`).

## Runtime readiness
- Run `php artisan config:cache` and `php artisan route:cache`.
- Ensure queue worker is running (e.g. `php artisan queue:work`).
- Ensure scheduler is running (cron calling `php artisan schedule:run`).
- Verify `/health` returns `200`.
- Run `php artisan system:status` and review output.
- Cloudron: run `php artisan cloudron:diag` and review output.

## Post-release
- Monitor logs and webhook delivery failures.
- Confirm agent heartbeat/license refresh are scheduled and succeeding.
- Cloudron: document update/rollback steps in release notes.
