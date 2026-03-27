FROM php:8.4-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends curl git libicu-dev libpq-dev libsqlite3-dev libzip-dev unzip zip \
    && docker-php-ext-install intl pdo pdo_pgsql pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

CMD ["./deploy/docker/entrypoint.sh"]
