FROM php:8.2-apache
WORKDIR /var/www/html
RUN apt-get update && \
    apt-get install -y libzip-dev && \
    docker-php-ext-install zip mysqli && \
    docker-php-ext-enable zip
EXPOSE 80