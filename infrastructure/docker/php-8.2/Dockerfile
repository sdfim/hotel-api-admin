# syntax=docker/dockerfile:1.4
ARG DATADOG_API_KEY

FROM php:8.2-apache-bookworm as baseapache2

RUN apt-get update && \
    apt-get install -y \
        git \
        zip \
        unzip \
        libicu-dev \
        libzip-dev \
        libwebp-dev \
        libjpeg-dev \
        libpng-dev \
        libfreetype6-dev \
        imagemagick \
        libmagickwand-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype && \
    docker-php-ext-install -j$(nproc) gd pdo_mysql mysqli zip intl bcmath sockets

# Installation of Redis from source
RUN git clone https://github.com/phpredis/phpredis.git /usr/src/php/ext/redis && \
    docker-php-ext-install -j$(nproc) /usr/src/php/ext/redis && \
    docker-php-ext-enable redis

# Installation of Imagick from source
RUN git clone https://github.com/Imagick/imagick.git /usr/src/php/ext/imagick && \
    docker-php-ext-install -j$(nproc) /usr/src/php/ext/imagick && \
    docker-php-ext-enable imagick

RUN sed -i 's/\/var\/www\/html/\/var\/www\/html\/public/g' /etc/apache2/sites-enabled/000-default.conf && \
    a2enmod rewrite headers

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# 1. Copy composer.json and composer.lock for caching composer dependencies
COPY composer.json composer.lock ./
# 2. Copy the entire application code. This makes 'artisan' available.
COPY . ./
# 3. Now run composer install. 'artisan' will be present for post-autoload-dump scripts.
RUN composer install --ignore-platform-reqs --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html && \
    cp .env.example .env && \
    php artisan key:generate

COPY infrastructure/docker/php-8.2/php-config.ini /usr/local/etc/php/conf.d/php-config.ini

# Copy the secret loading script
COPY load_secrets.php /var/www/html/load_secrets.php

COPY infrastructure/docker/php-8.2/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

FROM node:18-bookworm as npmbuild

WORKDIR /app

COPY --from=baseapache2 /var/www/html/package.json ./
COPY --from=baseapache2 /var/www/html/package-lock.json ./

RUN npm i

COPY --from=baseapache2 /var/www/html/ ./

RUN npm run build

FROM baseapache2

COPY --from=npmbuild /app/public/build/ /var/www/html/public/build/

EXPOSE 80
CMD ["apache2-foreground"]
ENTRYPOINT ["entrypoint.sh"]
