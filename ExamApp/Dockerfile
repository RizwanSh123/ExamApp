FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html
COPY . /var/www/html

RUN a2enmod rewrite

COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]

