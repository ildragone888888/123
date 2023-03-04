FROM php:7.4-cli
COPY . .
ENV WORKDIR /var/www/html/public
