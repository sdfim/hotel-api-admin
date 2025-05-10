#!/bin/bash

cd /var/www/html
php artisan optimize:clear
echo "Starting Laravel Optimize..." >&2
php artisan optimize
echo "Finished Laravel Optimize..." >&2
