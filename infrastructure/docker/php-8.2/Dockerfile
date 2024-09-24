ARG DATADOG_API_KEY

FROM php:8.2-apache-bookworm as baseapache2

RUN apt-get update && apt-get install -y gnupg git zip unzip libicu-dev libzip-dev && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql && docker-php-ext-install mysqli && docker-php-ext-install zip && docker-php-ext-configure intl && docker-php-ext-install intl && pecl install redis && docker-php-ext-enable redis && docker-php-ext-install bcmath

RUN sed -i 's/\/var\/www\/html/\/var\/www\/html\/public/g' /etc/apache2/sites-enabled/000-default.conf
RUN a2enmod rewrite headers

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ../../.. /var/www/html

RUN composer install --no-dev --optimize-autoloader

FROM node:18-bookworm as npmbuild

RUN mkdir -p /app
WORKDIR /app

COPY --from=baseapache2 /var/www/html/ /app/

RUN npm i && npm run build

FROM baseapache2

COPY --from=npmbuild /app/public/build/ /var/www/html/public/build/

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html
RUN cp .env.example .env
RUN cp infrastructure/docker/php-8.2/php-config.ini /usr/local/etc/php/conf.d/php-config.ini
RUN php artisan key:generate


###################################
###### INSTALL DATADOG AGENT ######
###################################

RUN sh -c "echo 'deb [signed-by=/usr/share/keyrings/datadog-archive-keyring.gpg] https://apt.datadoghq.com/ stable 7' > /etc/apt/sources.list.d/datadog.list"
RUN touch /usr/share/keyrings/datadog-archive-keyring.gpg \
    && chmod a+r /usr/share/keyrings/datadog-archive-keyring.gpg
RUN curl https://keys.datadoghq.com/DATADOG_APT_KEY_CURRENT.public | gpg --no-default-keyring --keyring /usr/share/keyrings/datadog-archive-keyring.gpg --import --batch
RUN curl https://keys.datadoghq.com/DATADOG_APT_KEY_C0962C7D.public | gpg --no-default-keyring --keyring /usr/share/keyrings/datadog-archive-keyring.gpg --import --batch
RUN curl https://keys.datadoghq.com/DATADOG_APT_KEY_F14F620E.public | gpg --no-default-keyring --keyring /usr/share/keyrings/datadog-archive-keyring.gpg --import --batch
RUN curl https://keys.datadoghq.com/DATADOG_APT_KEY_382E94DE.public | gpg --no-default-keyring --keyring /usr/share/keyrings/datadog-archive-keyring.gpg --import --batch
RUN apt-get update \
    && apt-get install datadog-agent datadog-signing-keys

RUN mkdir /etc/datadog-agent/conf.d/laravel.d
RUN cp infrastructure/docker/php-8.2/datadog/conf.yaml /etc/datadog-agent/conf.d/laravel.d

################################################################################################################################################

EXPOSE 80

ENTRYPOINT ["sh", "./infrastructure/docker/php-8.2/entrypoint.sh"]
