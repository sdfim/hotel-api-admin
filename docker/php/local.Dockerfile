FROM php:8.2-fpm-alpine

ARG TZ=Europe/Kiev
ARG user=app
ARG uid=1000

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apk update \
    && apk add --no-cache curl libpq-dev icu-dev zip unzip bash gmp-dev $PHPIZE_DEPS

RUN apk update && apk add --no-cache supervisor busybox-suid

RUN apk update && apk add --no-cache libzip-dev && docker-php-ext-configure zip && docker-php-ext-install zip

RUN set -eux \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && true \

RUN docker-php-ext-configure intl \
    && docker-php-ext-install pdo_pgsql intl bcmath opcache exif pcntl gmp

# setup GD extension
RUN apk add --no-cache \
      freetype \
      libjpeg-turbo \
      libpng \
      freetype-dev \
      libjpeg-turbo-dev \
      libpng-dev \
    && docker-php-ext-configure gd \
      --with-freetype=/usr/include/ \
      --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable gd \
    && apk del --no-cache \
      freetype-dev \
      libjpeg-turbo-dev \
      libpng-dev

# Install MySQL client and pdo_mysql extension
RUN apk add --no-cache mysql-client \
    && docker-php-ext-install pdo_mysql

RUN rm -rf /tmp/* /var/tmp/* \
    && docker-php-source delete

RUN adduser -G "www-data" -u $uid -D -h /home/$user $user

RUN mkdir -p /home/$user/.composer && \
    chown -R $uid:$uid /home/$user

WORKDIR /var/www/html

USER $user
