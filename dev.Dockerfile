FROM webdevops/php-nginx-dev:8.2

COPY infrastructure/docker/etc/supervisor/conf.d/laravel-scheduler.local.conf /opt/docker/etc/supervisor.d/laravel-scheduler.local.conf

COPY infrastructure/docker/etc/supervisor/conf.d/laravel-worker.local.conf /opt/docker/etc/supervisor.d/laravel-worker.local.conf

WORKDIR /app

RUN apt-get update \
    && curl -sLS https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
