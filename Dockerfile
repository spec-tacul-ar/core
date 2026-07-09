FROM php:8.4-cli-bookworm

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends git libsqlite3-dev libzip-dev unzip \
    && docker-php-ext-install bcmath pdo pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=node:22-bookworm-slim /usr/local/bin/ /usr/local/bin/
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules/ /usr/local/lib/node_modules/
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV APP_URL=http://localhost:8000

EXPOSE 8000
