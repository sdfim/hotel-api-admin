#!/bin/sh
set -eu

cp .env.example .env

composer install --no-dev --optimize-autoloader

MIGRATE_NOT_MIGRATED_STATUS=$(php artisan migrate:status | grep "not found" | wc -l)
if [ $MIGRATE_NOT_MIGRATED_STATUS = "1" ]; then
    php artisan migrate --seed
fi

npm i && npm run build

php artisan key:generate

php artisan queue:work

apache2-foreground
