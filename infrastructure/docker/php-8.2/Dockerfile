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

RUN git clone https://github.com/phpredis/phpredis.git /usr/src/php/ext/redis && \
    docker-php-ext-install -j$(nproc) /usr/src/php/ext/redis && \
    docker-php-ext-enable redis

RUN docker-php-ext-enable imagick opcache

RUN sed -i 's/\/var\/www\/html/\/var\/www\/html\/public/g' /etc/apache2/sites-enabled/000-default.conf && \
    a2enmod rewrite headers

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html
COPY composer.json composer.lock ./

RUN composer install --ignore-platform-reqs --no-dev --optimize-autoloader

COPY . ./

RUN chown -R www-data:www-data /var/www/html && \
    cp .env.example .env && \
    php artisan key:generate

COPY infrastructure/docker/php-8.2/php-config.ini /usr/local/etc/php/conf.d/php-config.ini
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
# ENTRYPOINT ["entrypoint.sh"]
