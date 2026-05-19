#!/bin/bash
set -e

cd /var/www/html

composer install --no-interaction --prefer-dist --optimize-autoloader

if [ ! -f .env ]; then
  cp .env.example .env
fi

php artisan key:generate --no-interaction --force

php artisan migrate --force

php artisan storage:link --force 2>/dev/null || true

exec php artisan serve --host=0.0.0.0 --port=8000
