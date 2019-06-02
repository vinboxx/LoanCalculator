FROM php:7.3.6-apache

COPY src/ /var/www/html/

EXPOSE 80/tcp
