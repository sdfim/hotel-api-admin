FROM php:8.2-cli

WORKDIR /var/www

RUN apt-get update && apt-get install -y git zip unzip cron \
    supervisor libicu-dev libzip-dev mariadb-client && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install zip && docker-php-ext-install pdo_mysql && docker-php-ext-install mysqli && docker-php-ext-configure intl && docker-php-ext-install intl && docker-php-ext-install bcmath && pecl install redis && docker-php-ext-enable redis && docker-php-ext-install sockets && docker-php-ext-configure sockets

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ../../.. /var/www

# Copy supervisord config for queue and cron management
RUN cp infrastructure/docker/php-8.2/supervisord.conf /etc/supervisord.conf
# Copy cron.d and cronenv if system cron is used within the container
RUN cp -r infrastructure/docker/php-8.2/cron.d /etc/
RUN cp infrastructure/docker/php-8.2/cronenv /cronenv

RUN composer install --ignore-platform-reqs --optimize-autoloader
RUN cp .env.example .env
RUN php artisan key:generate

RUN mkdir storage_fusemnt
RUN chown -R www-data:www-data /var/www
RUN chmod +x /var/www/infrastructure/docker/php-8.2/start.sh

# No EXPOSE 80 as this is not a web-serving container
ENTRYPOINT ["/var/www/infrastructure/docker/php-8.2/start.sh"]
