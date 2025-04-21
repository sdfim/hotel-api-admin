#!/bin/sh

cd /var/www
php artisan migrate
php artisan db:seed
/usr/bin/supervisord -c /etc/supervisord.conf
