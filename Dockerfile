FROM php:8.1-apache

WORKDIR /var/www/html/
COPY src/ .
RUN mv connect_xampp.inc.php connect.inc.php \
    && sed -i -e "s/localhost/db/g" connect.inc.php
RUN docker-php-ext-install mysqli
EXPOSE 80
