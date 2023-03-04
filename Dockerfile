FROM php:7.4-cli

COPY . .

# Image config

ENV WEBROOT /var/www/html/public
ENV RUN_SCRIPTS 1

# Laravel config
ENV APP_ENV production
ENV LOG_CHANNEL stderr
