FROM php:8.1-fpm

RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev libpq-dev zip \
    && docker-php-ext-install intl opcache pdo pdo_mysql pdo_pgsql pgsql\
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

WORKDIR /var/www/symfony_docker

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug
