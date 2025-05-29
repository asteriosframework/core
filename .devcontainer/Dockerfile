FROM php:8.3-alpine

# Install necessary packages
RUN apk update && \
    apk add --no-cache \
        mariadb-client \
        nginx \
        supervisor \
        bash \
        libzip-dev \
        oniguruma-dev \
        autoconf \
        gcc \
        g++ \
        make \
        openssl-dev \
        pkgconfig \
        linux-headers

# Install PHP extensions for MySQL/MariaDB
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Install Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Set working directory
WORKDIR /var/www

# Copy nginx config (wird später beim Build hinzugefügt)
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Expose HTTP port
EXPOSE 80

# Copy supervisor config (wird später beim Build hinzugefügt)
COPY supervisord.conf /etc/supervisord.conf

# Start services using supervisord
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]