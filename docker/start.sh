#!/bin/sh

cd /var/www
php artisan migrate --seed
/usr/bin/supervisord -c /etc/supervisord.conf
