# syntax=docker/dockerfile:1.4
ARG DATADOG_API_KEY

FROM php:8.2-apache-bookworm as baseapache2

# Кэшируем установку системных пакетов
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    apt-get update && \
    apt-get install -y --no-install-recommends \
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
        pkg-config

# GD +
RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype && \
    docker-php-ext-install -j$(nproc) gd pdo_mysql mysqli zip intl bcmath sockets

# Используем PECL вместо git clone для кэширования
RUN pecl install redis-6.0.2 && docker-php-ext-enable redis
RUN pecl install imagick-3.7.0 && docker-php-ext-enable imagick

RUN sed -i 's/\/var\/www\/html/\/var\/www\/html\/public/g' /etc/apache2/sites-enabled/000-default.conf && \
    a2enmod rewrite headers

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# Кэшируем composer dependencies
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/root/.composer \
    composer install --ignore-platform-reqs --no-dev --no-scripts --no-autoloader

# Копируем код после установки зависимостей
COPY . ./

RUN composer dump-autoload --optimize --no-dev

RUN chown -R www-data:www-data /var/www/html && \
    cp .env.example .env && \
    php artisan key:generate

COPY infrastructure/docker/php-8.2/php-config.ini /usr/local/etc/php/conf.d/php-config.ini

# Copy the secret loading script
COPY infrastructure/docker/php-8.2/load_secrets.php /var/www/html/load_secrets.php

COPY infrastructure/docker/php-8.2/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# NPM Build stage
FROM node:18-bookworm as npmbuild

WORKDIR /app

COPY --from=baseapache2 /var/www/html/package.json ./
COPY --from=baseapache2 /var/www/html/package-lock.json ./

# Кэшируем npm install
RUN --mount=type=cache,target=/root/.npm \
    npm ci

COPY --from=baseapache2 /var/www/html/ ./

RUN npm run build

# Final stage
FROM baseapache2

COPY --from=npmbuild /app/public/build/ /var/www/html/public/build/

EXPOSE 80
CMD ["apache2-foreground"]
ENTRYPOINT ["entrypoint.sh"]
