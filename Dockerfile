FROM php:7.3-alpine

#install all the system dependencies and enable PHP modules 
RUN apk add --update \
    autoconf \
    git \
    icu-dev \
    libzip-dev \
    php7-curl \
    php7-intl \
    php7-mbstring \
    php7-mysqli \
    php7-opcache \
    php7-openssl \
    php7-pdo_mysql \
    php7-pdo_pgsql \
    php7-pgsql \
    php7-zip \
    php7-zlib \
    postgresql-dev \
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
  && rm -rf /var/cache/apk/*

#install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

COPY . /var/www/html

WORKDIR /var/www/html

# install all PHP dependencies
RUN composer install --no-interaction

# Modify app.php file
RUN sed -i -e "s/'SECURITY_SALT'/'SECURITY_SALT', '5C2Yi3REBrXA5cN06dcH6VdAeJySm6RR'/" config/app.php && \
	# Make sessionhandler configurable via environment
	sed -i -e "s/'php',/env('SESSION_DEFAULTS', 'php'),/" config/app.php
	# Set write permissions for webserver

EXPOSE 80

CMD bin/cake server -H 0.0.0.0 -p 80
