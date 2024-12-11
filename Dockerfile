FROM php:8.1-apache


RUN apt-get update && apt-get install -y \
		libxml2-dev libmagickwand-dev git unzip
RUN docker-php-ext-install soap pdo pdo_mysql
RUN pecl install imagick && docker-php-ext-enable imagick

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY . /var/www/html

# Enable mod_rewrite for images with apache
RUN if command -v a2enmod >/dev/null 2>&1; then \
        a2enmod rewrite headers \
    ;fi

RUN composer install