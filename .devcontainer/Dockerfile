FROM php:8.3-fpm-alpine
ARG UNAME=vscode
ARG UID=1000
ARG GID=1000

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
    shadow \
    python3 \
    py3-pip

# Install PHP extensions for MySQL/MariaDB
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Install Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Create vscode user with home directory
RUN groupadd -g $GID -o $UNAME && useradd -m -u $UID -g $GID -s /bin/bash $UNAME

# Install pre-commit in user-local directory to avoid PEP 668 error
RUN python -m venv /home/$UNAME/.venv && \
    . /home/$UNAME/.venv/bin/activate && \
    /home/$UNAME/.venv/bin/pip install --upgrade pip && \
    pip install pre-commit && \
    deactivate
RUN chown -R $UNAME:$UNAME /home/$UNAME/.venv

# Set working directory
WORKDIR /var/www

# Expose PHP-FPM port
EXPOSE 9000
