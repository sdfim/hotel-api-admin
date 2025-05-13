#!/bin/bash

cd /var/www/html

composer dump-autoload --optimize

# Reset OPcache if available
php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache reset.'; }"

# Reset caches at runtime when env vars are available
php artisan optimize:clear
php artisan cache:clear
php artisan view:clear
php artisan event:clear
php artisan config:clear # Crucial if .env could have changed
php artisan route:clear

echo "Starting Laravel Optimize..." >&2

php artisan optimize

echo "Finished Laravel Optimize..." >&2

sudo systemctl restart apache2 # Common for Apache setups
