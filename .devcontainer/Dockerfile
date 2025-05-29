FROM php:8.3-fpm-alpine

# Install necessary packages
RUN apk update && \
    apk add --no-cache \
        mariadb-client \
        bash \
        git \
        curl \
        libzip-dev \
        oniguruma-dev \
        autoconf \
        gcc \
        g++ \
        make \
        openssl-dev \
        pkgconfig \
        linux-headers \
        shadow

# Install PHP extensions for MySQL/MariaDB
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Install Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Create vscode user with home directory
RUN useradd -m -s /bin/bash vscode

# Set working directory
WORKDIR /var/www

# Expose PHP-FPM port
EXPOSE 9000
