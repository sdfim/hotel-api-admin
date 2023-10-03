#!/bin/sh
# testing
ping -c 2 test.ean.com
echo 'Check curl test.ean.com:'; curl --connect-timeout 5 -I https://test.ean.com
ping -c 2 8.8.8.8
ping -c 2 google.com
echo 'Check curl google.com:'; curl --connect-timeout 5 -I https://www.google.com
ping -c 1 ujv-rds-dev.cxrnm2ba2jtz.us-east-1.rds.amazonaws.com
# testing

cd /var/www
php artisan migrate
/usr/bin/supervisord -c /etc/supervisord.conf
