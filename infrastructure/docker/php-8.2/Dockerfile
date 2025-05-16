ARG DATADOG_API_KEY

FROM php:8.2-apache-bookworm as baseapache2

RUN apt-get update && apt-get install -y git zip unzip libicu-dev libzip-dev libwebp-dev && rm -rf /var/lib/apt/lists/*

# Install system dependencies for GD
RUN apt-get update && apt-get install -y \
    libjpeg-dev \
    libpng-dev \
    libwebp-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install gd

RUN docker-php-ext-install pdo_mysql && docker-php-ext-install mysqli && docker-php-ext-install zip && docker-php-ext-configure intl && docker-php-ext-install intl && pecl install redis && docker-php-ext-enable redis && docker-php-ext-install bcmath && docker-php-ext-install sockets && docker-php-ext-configure sockets

# Install Imagick
RUN apt-get update \
    && apt-get install -y imagemagick \
    libmagickwand-dev \
    libwebp-dev \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install OpCache
RUN docker-php-ext-enable opcache

RUN sed -i 's/\/var\/www\/html/\/var\/www\/html\/public/g' /etc/apache2/sites-enabled/000-default.conf
RUN a2enmod rewrite headers

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ../../.. /var/www/html

RUN composer install --ignore-platform-reqs --no-dev --optimize-autoloader

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
RUN cp infrastructure/docker/php-8.2/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
RUN php artisan key:generate

EXPOSE 80

CMD ["apache2-foreground"]
# ENTRYPOINT ["entrypoint.sh"]
