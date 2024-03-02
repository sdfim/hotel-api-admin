FROM php:8.2-fpm

WORKDIR /var/www

#ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN apt-get update && apt-get install -y git zip unzip nginx cron \
    supervisor libicu-dev libzip-dev && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install zip && docker-php-ext-install pdo_mysql && docker-php-ext-install mysqli && docker-php-ext-configure intl && docker-php-ext-install intl && docker-php-ext-install bcmath && pecl install redis && docker-php-ext-enable redis

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ../../.. /var/www

RUN cp docker/supervisord.conf /etc/supervisord.conf
RUN cp docker/php-tasks.ini /usr/local/etc/php/conf.d/app.ini
RUN cp docker/nginx.conf /etc/nginx/sites-enabled/default
RUN cp -r docker/cron.d /etc/
RUN cp docker/cronenv /cronenv

RUN composer install --no-dev --optimize-autoloader
RUN mv .env.example .env
RUN php artisan key:generate

RUN mkdir storage_fusemnt
RUN chown -R www-data:www-data /var/www
RUN chmod +x /var/www/docker/start.sh
RUN sed -i 's/;clear_env = no/clear_env = no/' /usr/local/etc/php-fpm.d/www.conf

EXPOSE 80
ENTRYPOINT ["/var/www/docker/start.sh"]
