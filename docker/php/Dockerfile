FROM php:8.3-fpm

RUN apt-get update && apt-get install -y acl libxml2-dev

RUN docker-php-ext-install pdo pdo_mysql ctype dom iconv

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/symfony