FROM php:8.3-fpm

WORKDIR /var/www

# Install system packages and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip curl git \
    libicu-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
