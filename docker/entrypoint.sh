#!/bin/sh
# Bootstraps the app on container start: dependencies, .env, key, database, storage link.
set -e
cd /var/www/html

[ -f .env ] || cp .env.example .env
[ -d vendor ] || composer install --no-interaction --prefer-dist
grep -q '^APP_KEY=.\+' .env || php artisan key:generate --force

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Running migrations (waiting for database)..."
    i=0
    until php artisan migrate --force; do
        i=$((i+1)); [ "$i" -gt 30 ] && { echo "Database not reachable"; exit 1; }
        sleep 3
    done
fi

[ -L public/storage ] || php artisan storage:link --force

exec "$@"
