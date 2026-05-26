# https://hub.docker.com/_/php
# https://github.com/mlocati/docker-php-extension-installer

FROM php:8.3-fpm

# Add checksum for security
ADD --chmod=755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Connect PHP Extensions Installation
RUN install-php-extensions \
    @composer \
    gd \
    curl \
    dom \
    xml \
    simplexml \
    pcntl \
    zip \
    bcmath \
    imap \
    pdo_mysql \
    mysqli \
    redis

# Combine system installation and cleaning
RUN apt-get update && \
    apt-get install -y \
    git \
    cron \
    sudo && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    usermod -aG sudo www-data && \
    echo "www-data ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers

# Configuring directories in one layer
RUN rm -rf /var/www/html && \
    mkdir /var/www/api && \
    chown -R www-data:www-data /var/www/api

WORKDIR /var/www/api

COPY --chmod=755 setup.sh /usr/local/bin/setup.sh

ENTRYPOINT ["/usr/local/bin/setup.sh"]
