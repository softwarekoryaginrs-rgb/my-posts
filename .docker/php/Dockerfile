FROM php:8.5-fpm-alpine AS app
ENV APP_ENV=dev
WORKDIR /var/www/app

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN apk add --no-cache \
    build-base pkgconfig zlib-dev g++ git icu-dev icu-data-full zip libzip-dev libpq-dev autoconf make \
    && docker-php-ext-install intl pdo pdo_pgsql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

RUN composer install --no-scripts

EXPOSE 9000
CMD ["php-fpm", "--nodaemonize"]