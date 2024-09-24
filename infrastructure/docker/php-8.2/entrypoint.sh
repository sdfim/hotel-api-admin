#!/bin/sh

# Run additional scripts
if [ -d "infrastructure/docker/php-8.2/scripts" ]
then
    find infrastructure/docker/php-8.2/scripts/ -name \*.\* | while read file
    do
        echo "-------------------------------------"
        echo "RUNNING FILE: $file"
        echo "-------------------------------------"
        sh "./$file"
    done
fi

# Apache foreground
/bin/sh /usr/sbin/apache2ctl -D FOREGROUND
