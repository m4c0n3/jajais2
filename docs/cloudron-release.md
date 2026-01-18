# Cloudron Release & Update Runbook

## Versioning
- Tag every release: `git tag -a vX.Y.Z -m "Release vX.Y.Z"`.
- Write release notes using `docs/release-notes-template.md`.

## Build
- `cloudron build` or `docker build -f cloudron/Dockerfile -t jajais-cloudron .`
- Verify `/health` on a staging install.

## Update
- Use Cloudron update workflow (`cloudron update`).
- `start.sh` runs migrations (`php artisan migrate --force`).
- Review logs and run `php artisan cloudron:diag` post-update.

## Rollback
- If the update fails, rollback via Cloudron restore/rollback.
- Verify `/health` and `php artisan cloudron:diag`.

## Migration notes
- Migrations run on every start; avoid breaking changes without a rollback plan.
- For destructive migrations, plan a maintenance window and backup first.
