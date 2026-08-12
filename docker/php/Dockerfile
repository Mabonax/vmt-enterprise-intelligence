FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libpq-dev \
    linux-headers \
    oniguruma-dev \
    postgresql-client \
    unzip \
    zip \
    && docker-php-ext-install bcmath intl opcache pcntl pdo_pgsql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/vip.ini
