#!/bin/sh

cd /var/www
echo "Starting Laravel Optimize..." >&2
php artisan optimize
echo "Finished Laravel Optimize..." >&2
