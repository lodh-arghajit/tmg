FROM php:8.0-apache

RUN apt-get update
RUN apt-get install git -y
RUN apt-get install zlib1g-dev -y
RUN apt-get install libpng-dev -y
RUN apt-get install libjpeg-dev -y
RUN apt-get install libzip-dev -y
RUN apt-get install unzip -y
RUN apt-get install curl -y

RUN docker-php-ext-install zip

RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-configure gd --with-jpeg=/usr/include/ &&\
    docker-php-ext-install gd

COPY composer.json /var/www/html
COPY composer.lock /var/www/html
COPY composer.phar /var/www/html
WORKDIR /var/www/html
RUN php composer.phar --version
COPY web/modules/custom /var/www/html/web/modules/custom
COPY web/themes/custom /var/www/html/web/themes/custom
RUN php composer.phar install
COPY web/sites/default/settings.php /var/www/html/web/sites/default
COPY web/sites/default/settings.local.php /var/www/html/web/sites/default
