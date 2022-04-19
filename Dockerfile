FROM php:8.0-apache

RUN apt-get update
RUN apt-get install git -y
RUN apt-get install zlib1g-dev -y
RUN apt-get install libpng-dev -y
RUN apt-get install libjpeg-dev -y
RUN apt-get install libzip-dev -y
RUN apt-get install unzip -y
RUN apt-get install curl -y
RUN apt-get install mariadb-client -y
RUN apt-get install vim -y

RUN docker-php-ext-install zip

RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-configure gd --with-jpeg=/usr/include/ &&\
    docker-php-ext-install gd

COPY composer.json /var/www/html
COPY db.sql /var/www/html
COPY composer.lock /var/www/html
COPY vendor /var/www/html/vendor
COPY web /var/www/html/web
COPY inline-images /var/www/html/web/sites/default/files
COPY load.environment.php /var/www/html


WORKDIR /var/www/html
ENV APACHE_DOCUMENT_ROOT=/var/www/html/web
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 80
