FROM php:7.4-apache
COPY . .
WORKDIR /var/www/html/public
EXPOSE 80 
EXPOSE 443
