FROM php:8.2-fpm-alpine AS deps

WORKDIR /app

RUN apk add --no-cache \
    build-base \
    postgresql-dev \
    sqlite-dev \
    zip \
    git \
    curl

RUN docker-php-ext-install -j$(nproc) pdo pdo_pgsql pdo_sqlite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

FROM php:8.2-fpm-alpine

WORKDIR /app

RUN apk add --no-cache \
    postgresql-libs \
    sqlite-libs \
    curl \
    nodejs \
    npm \
    libpq

COPY --from=deps /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=deps /usr/local/etc/php /usr/local/etc/php
COPY --from=deps /app/vendor ./vendor

COPY . .

RUN npm install && npm run build

RUN mkdir -p storage/logs storage/framework/sessions storage/framework/views storage/framework/cache && \
    chown -R www-data:www-data storage bootstrap && \
    chmod -R 775 storage bootstrap

EXPOSE 9000

CMD ["php-fpm"]
