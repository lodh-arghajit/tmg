FROM drupal:9.2.5-php7.4-apache

RUN docker-php-ext-install mysqli
RUN apt update
RUN apt install git -y
COPY --chown=www-data:www-data .. /var/www/html
WORKDIR /var/www/
COPY . .
RUN composer install
RUN ln -s $(pwd)/vendor/bin/drush /usr/local/bin/drush
RUN apache2ctl restart

EXPOSE 80
