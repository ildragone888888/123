FROM php:7.2-apache

COPY . .

# Image config
ENV WEBROOT /var/www/html/public
