#!/bin/sh

cd /var/www
php artisan migrate
/usr/bin/supervisord -c /etc/supervisord.conf
