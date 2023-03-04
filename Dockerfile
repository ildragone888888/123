FROM richarvey/nginx-php-fpm:1.9.1
COPY . .
# Image config
ENV WEBROOT /var/www/html/public
ENV RUN_SCRIPTS 1
# Laravel config
ENV APP_ENV production
ENV LOG_CHANNEL stderr
