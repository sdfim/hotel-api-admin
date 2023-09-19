FROM node:18-bookworm as npmbuild

RUN mkdir -p /app
WORKDIR /app

COPY . /app/

RUN npm i && npm run build

FROM php:8.2-apache-bookworm

RUN apt-get update && apt-get install -y git zip unzip && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql && docker-php-ext-install mysqli

RUN sed -i 's/\/var\/www\/html/\/var\/www\/html\/public/g' /etc/apache2/sites-enabled/000-default.conf
RUN a2enmod rewrite headers

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /var/www/html
COPY --from=npmbuild /app/public/build/ /var/www/html/public/build/

WORKDIR /var/www/html

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html
RUN mv .env.example .env
RUN php artisan key:generate

EXPOSE 80

CMD ["apache2-foreground"]
