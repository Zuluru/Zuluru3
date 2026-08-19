FROM php:7.4-cli-bullseye

# System dependencies.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libonig-dev \
        libzip-dev \
        zlib1g-dev \
        libpq-dev \
        default-mysql-client \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-install \
        intl \
        mbstring \
        pcntl \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
        zip \
        opcache \
    && rm -rf /var/lib/apt/lists/*

# Install Composer.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_HOME=/tmp/composer \
    COMPOSER_CACHE_DIR=/tmp/composer/cache \
    COMPOSER_ALLOW_SUPERUSER=1
RUN mkdir -p /tmp/composer/cache && chmod -R 0777 /tmp/composer

WORKDIR /var/www/html
COPY docker/entrypoint.sh /usr/local/bin/zuluru-entrypoint
RUN chmod +x /usr/local/bin/zuluru-entrypoint
EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/zuluru-entrypoint"]
CMD ["bin/cake", "server", "-H", "0.0.0.0", "-p", "8080"]
