FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get clean

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --from=composer:2.9.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY ../composer.* ./

RUN composer install --prefer-dist --no-dev --no-scripts --no-progress --no-interaction

COPY ../ .

RUN composer dump-autoload --optimize