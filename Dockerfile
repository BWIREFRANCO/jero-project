# /var/www/jero.com/Dockerfile
FROM php:8.2-apache
# Install the MySQL extension for PHP
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
