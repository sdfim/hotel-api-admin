#!/bin/bash

cd /var/www/html

composer dump-autoload --optimize

# Reset caches at runtime when env vars are available
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize:clear

# Reset OPcache if available
php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache reset.'; }"

echo "Starting Laravel Optimize..." >&2
# php artisan optimize
echo "Finished Laravel Optimize..." >&2
