FROM webdevops/php-apache:8.2

RUN apt-get update \
    && curl -sLS https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY ./ /app

WORKDIR /app

RUN npm i && npm run build

RUN mv .env.example .env
RUN cp docker/php-config.ini /usr/local/etc/php/conf.d/php-config.ini
RUN php artisan key:generate

