FROM php:8.0-apache
COPY . .
WORKDIR /var/www/html/public
EXPOSE 80
