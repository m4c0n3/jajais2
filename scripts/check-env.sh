#!/usr/bin/env bash
set -euo pipefail

MIN_PHP_MAJOR=8
MIN_PHP_MINOR=4

fail() {
  echo "ERROR: $1" >&2
  exit 1
}

command -v php >/dev/null 2>&1 || fail "php not found in PATH"
command -v composer >/dev/null 2>&1 || fail "composer not found in PATH"

PHP_VERSION=$(php -r 'echo PHP_VERSION;')
PHP_MAJOR=$(php -r 'echo PHP_MAJOR_VERSION;')
PHP_MINOR=$(php -r 'echo PHP_MINOR_VERSION;')

if [ "$PHP_MAJOR" -lt "$MIN_PHP_MAJOR" ] || { [ "$PHP_MAJOR" -eq "$MIN_PHP_MAJOR" ] && [ "$PHP_MINOR" -lt "$MIN_PHP_MINOR" ]; }; then
  fail "PHP $MIN_PHP_MAJOR.$MIN_PHP_MINOR+ required, found $PHP_VERSION"
fi

echo "OK: PHP $PHP_VERSION"
composer -V >/dev/null 2>&1 && echo "OK: composer available"

REQUIRED_EXTS=(
  openssl
  pdo
  pdo_sqlite
  mbstring
  tokenizer
  xml
  ctype
  json
)

MISSING=()
for ext in "${REQUIRED_EXTS[@]}"; do
  if ! php -m | tr '[:upper:]' '[:lower:]' | grep -qx "$ext"; then
    MISSING+=("$ext")
  fi
done

if [ "${#MISSING[@]}" -gt 0 ]; then
  fail "Missing PHP extensions: ${MISSING[*]}"
fi

echo "OK: required PHP extensions present"

for dir in storage bootstrap/cache; do
  if [ ! -d "$dir" ]; then
    fail "Missing directory: $dir"
  fi
  if [ ! -w "$dir" ]; then
    fail "Directory not writable: $dir"
  fi
done

echo "OK: storage/ and bootstrap/cache are writable"

echo "Environment check passed."
