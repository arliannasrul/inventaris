#!/usr/bin/env sh
set -e

if [ ! -f .env ]; then
  cp .env.example .env
fi

mkdir -p storage/app storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force
fi

php artisan config:clear >/dev/null 2>&1 || true

# Ensure SQLite file exists if connection is SQLite
if [ "$DB_CONNECTION" = "sqlite" ]; then
  touch database/database.sqlite
fi

php artisan migrate --force

exec "$@"
