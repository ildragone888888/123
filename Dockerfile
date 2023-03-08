FROM php:8.1-apache
COPY . .
WORKDIR /var/www/html/public
EXPOSE 80
