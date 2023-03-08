FROM php:7.4-apache
COPY . .
WORKDIR /var/www/html
EXPOSE 80 
#EXPOSE 443
