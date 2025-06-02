FROM php:8.3-fpm-alpine
ARG UNAME
ARG UID
ARG GID

# Install necessary packages
RUN apk update && \
    apk add --no-cache \
    composer \
    mariadb-client \
    openssh-client \
    bash \
    git \
    curl \
    gpg \
    gpg-agent \
    keychain \
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
    py3-pip \
    nodejs \
    npm

RUN apk add composer \
    php83-curl \
    php83-iconv \
    php83-mbstring \
    php83-openssl \
    php83-zip \
    php83-phar \
    php83-mysqli \
    php83-pdo \
    php83-pdo_mysql \
    php83-sodium \
    php83-pecl-xdebug \
    php83-tokenizer \
    php83-dom \
    php83-xml \
    php83-xmlreader \
    php83-xmlwriter \
    --repository https://dl-cdn.alpinelinux.org/alpine/edge/community
RUN apk add libcurl \
    --repository https://dl-cdn.alpinelinux.org/alpine/edge/main

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

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
