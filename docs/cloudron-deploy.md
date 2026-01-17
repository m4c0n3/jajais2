# Cloudron Deployment

## Build
- `docker build -f cloudron/Dockerfile -t jajais-cloudron .`

## Install
- Create a new Cloudron app using the built image.
- Visit `/install` for first-run setup, or set `APP_MODE` env for non-interactive mode.

## Environment
Cloudron addons provide:
- MySQL: `MYSQL_HOST`, `MYSQL_USERNAME`, `MYSQL_PASSWORD`, `MYSQL_DATABASE`, `MYSQL_PORT`
- Redis: `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT`

The start script wires these into Laravel environment variables without logging secrets.

## Storage
- Persistent data lives in `/app/data`.
- `storage/` and `bootstrap/cache` are symlinked to `/app/data`.

## Health
- Health check: `/health`
