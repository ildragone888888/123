FROM richarvey/nginx-php-fpm:1.9.1
COPY . .
# Image config
ENV WEBROOT /var/www/html/public

