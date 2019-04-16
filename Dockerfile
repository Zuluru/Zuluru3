FROM php:7-apache

#install all the system dependencies and enable PHP modules 
RUN apt-get update && apt-get install -y \
      libicu-dev \
      libpq-dev \
      libmcrypt-dev \
      zlib1g-dev \
      libzip-dev \
      mysql-client \
      git \
      zip \
      unzip \
    && pecl install mcrypt-1.0.2 \
    && docker-php-ext-enable mcrypt \
    && rm -r /var/lib/apt/lists/* \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-install \
      intl \
      mbstring \
      pcntl \
      pdo_mysql \
      pdo_pgsql \
      pgsql \
      zip \
      opcache

#install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

#set our application folder as an environment variable
ENV APP_HOME /var/www/html

#change uid and gid of apache to docker user uid/gid
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

#change the web_root to cakephp /var/www/html/webroot folder
RUN sed -i -e "s/html/html\/webroot/g" /etc/apache2/sites-enabled/000-default.conf

# enable apache module rewrite
RUN a2enmod rewrite

#copy source files and run composer
COPY . $APP_HOME

# install all PHP dependencies
RUN composer install --no-interaction

# Modify app.php file
RUN sed -i -e "s/'SECURITY_SALT'/'SECURITY_SALT', '5C2Yi3REBrXA5cN06dcH6VdAeJySm6RR'/" config/app.php && \
	# Make sessionhandler configurable via environment
	sed -i -e "s/'php',/env('SESSION_DEFAULTS', 'php'),/" config/app.php  && \
	# Set write permissions for webserver
	chgrp -R www-data logs tmp config upload && \
	chmod -R g+rw logs tmp config upload

#change ownership of our applications
RUN chown -R www-data:www-data $APP_HOME
