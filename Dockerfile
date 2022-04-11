FROM php:8.0-apache

#### Install Composer ####
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --quiet
RUN RESULT=$?
RUN rm composer-setup.php
RUN mv composer.phar /usr/local/bin/composer
RUN exit $RESULT
### End Composer install ###
RUN apt-get update
RUN apt-get install git -y
RUN apt-get install zlib1g-dev -y
RUN apt-get install libpng-dev -y
RUN apt-get install libjpeg-dev -y
RUN apt-get install libzip-dev -y
RUN apt-get install unzip -y

RUN docker-php-ext-install zip

RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-configure gd --with-jpeg=/usr/include/ &&\
    docker-php-ext-install gd
RUN composer require cweagans/composer-patches
COPY composer.json /var/www/html
COPY composer.lock /var/www/html
WORKDIR /var/www/html
COPY web/modules/custom /var/www/html/web/modules/custom
COPY web/themes/custom /var/www/html/web/themes/custom
RUN composer install
