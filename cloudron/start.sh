#!/bin/bash
set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then
  echo "ERROR: start.sh must run as root" >&2
  exit 1
fi

APP_DIR="/app/code"
DATA_DIR="/app/data"

mkdir -p "${DATA_DIR}/storage" "${DATA_DIR}/bootstrap-cache" "${DATA_DIR}/tmp"

if [ ! -L "${APP_DIR}/storage" ]; then
  rm -rf "${APP_DIR}/storage" || true
  ln -s "${DATA_DIR}/storage" "${APP_DIR}/storage"
fi

if [ ! -L "${APP_DIR}/bootstrap/cache" ]; then
  rm -rf "${APP_DIR}/bootstrap/cache" || true
  ln -s "${DATA_DIR}/bootstrap-cache" "${APP_DIR}/bootstrap/cache"
fi

if [ ! -f "${DATA_DIR}/.env" ]; then
  cp "${APP_DIR}/.env.example" "${DATA_DIR}/.env"
fi

if [ ! -L "${APP_DIR}/.env" ]; then
  rm -f "${APP_DIR}/.env" || true
  ln -s "${DATA_DIR}/.env" "${APP_DIR}/.env"
fi

export APP_ENV=production
export APP_DEBUG=false
export APP_URL="https://${CLOUDRON_APP_DOMAIN}"

export DB_CONNECTION=mysql
export DB_HOST="${MYSQL_HOST}"
export DB_PORT="${MYSQL_PORT}"
export DB_DATABASE="${MYSQL_DATABASE}"
export DB_USERNAME="${MYSQL_USERNAME}"
export DB_PASSWORD="${MYSQL_PASSWORD}"

export REDIS_HOST="${REDIS_HOST:-127.0.0.1}"
export REDIS_PORT="${REDIS_PORT:-6379}"
export REDIS_PASSWORD="${REDIS_PASSWORD:-}"
export CACHE_STORE=redis
export QUEUE_CONNECTION=redis
export SESSION_DRIVER=database

export APP_MODE="${APP_MODE:-}"

if [ -z "${APP_KEY:-}" ]; then
  gosu cloudron:cloudron php "${APP_DIR}/artisan" key:generate --force
fi

gosu cloudron:cloudron php "${APP_DIR}/artisan" migrate --force

gosu cloudron:cloudron php -S 0.0.0.0:8000 -t "${APP_DIR}/public"
