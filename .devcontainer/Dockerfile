FROM alpine:latest
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

RUN apk add php83 \
    php83-fpm \
    php83-bcmath \
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
    php83-simplexml \
    php83-json \
    php83-xml \
    php83-xmlreader \
    php83-xmlwriter \
    php83-pecl-ast \
    --repository https://dl-cdn.alpinelinux.org/alpine/edge/community

RUN apk add libcurl \
    --repository https://dl-cdn.alpinelinux.org/alpine/edge/main

# User mit /bin/bash als Shell anlegen (funktioniert, wenn bash installiert ist)
RUN groupadd -g $GID -o $UNAME && useradd -m -u $UID -g $GID -s /bin/bash $UNAME

# Install pre-commit in user-local directory to avoid PEP 668 error
RUN python3 -m venv /home/$UNAME/.venv && \
    . /home/$UNAME/.venv/bin/activate && \
    /home/$UNAME/.venv/bin/pip install --upgrade pip && \
    pip install pre-commit && \
    deactivate
RUN chown -R $UNAME:$UNAME /home/$UNAME/.venv /home/vscode /etc/php83 /var/log/php83

# Optional: eigene php.ini kopieren
# COPY php.ini /etc/php83/php.ini

RUN sed -i 's/^user = .*/user = vscode/' /etc/php83/php-fpm.d/www.conf && \
    sed -i 's/^group = .*/group = vscode/' /etc/php83/php-fpm.d/www.conf && \
    sed -i -E -e 's/^;(zend_extension=xdebug\.so)$/\1/g' /etc/php83/conf.d/50_xdebug.ini

WORKDIR /var/www
USER $UNAME

EXPOSE 9000

CMD ["php-fpm83", "--nodaemonize", "--fpm-config", "/etc/php83/php-fpm.conf"]
