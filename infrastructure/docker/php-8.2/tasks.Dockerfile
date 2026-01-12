FROM php:8.2-cli

WORKDIR /var/www

# ✅ Шаг 1: Установка системных пакетов - будет кэшироваться
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    apt-get update && apt-get install -y --no-install-recommends \
        git \
        zip \
        unzip \
        cron \
        supervisor \
        libicu-dev \
        libzip-dev \
        mariadb-client \
        libjpeg-dev \
        libpng-dev \
        libwebp-dev \
        libfreetype6-dev

# ✅ Шаг 2: Установка PHP расширений - будет кэшироваться
RUN docker-php-ext-install -j$(nproc) zip pdo_mysql mysqli bcmath sockets intl && \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install -j$(nproc) gd

# ✅ Шаг 3: Установка Redis через PECL с версией - будет кэшироваться
RUN pecl install redis-6.0.2 && \
    docker-php-ext-enable redis

# ✅ Шаг 4: Установка Composer - будет кэшироваться
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ✅ Шаг 5: Копируем ТОЛЬКО composer файлы для кэширования зависимостей
COPY composer.json composer.lock ./

# ✅ Шаг 6: Устанавливаем composer зависимости БЕЗ кода - будет кэшироваться
RUN --mount=type=cache,target=/root/.composer \
    composer install --ignore-platform-reqs --no-scripts --no-autoloader --no-dev

# ✅ Шаг 7: Копируем конфиги ДО копирования всего кода
COPY infrastructure/docker/php-8.2/supervisord.conf /etc/supervisord.conf
COPY infrastructure/docker/php-8.2/php-config.ini /usr/local/etc/php/conf.d/99-memory-limit.ini
COPY infrastructure/docker/php-8.2/cron.d /etc/cron.d/
COPY infrastructure/docker/php-8.2/cronenv /cronenv
COPY infrastructure/docker/php-8.2/start.sh /var/www/start.sh

# ✅ Шаг 8: Копируем весь код ПОСЛЕ установки зависимостей
COPY . /var/www

# ✅ Шаг 9: Генерируем autoload после копирования кода
RUN composer dump-autoload --optimize --no-dev

# ✅ Шаг 10: Финальные настройки
RUN cp .env.example .env && \
    php artisan key:generate && \
    mkdir -p storage_fusemnt && \
    chown -R www-data:www-data /var/www && \
    chmod +x /var/www/start.sh

ENTRYPOINT ["/var/www/start.sh"]
