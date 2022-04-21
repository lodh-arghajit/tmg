FROM devwithlando/php:7.4-apache-2

COPY composer.json /var/www/html
COPY db.sql /var/www/html
COPY composer.lock /var/www/html
COPY web/modules/custom /var/www/html/web/modules/custom
COPY web/themes/custom /var/www/html/web/themes/custom

COPY load.environment.php /var/www/html


# RUN chown -R www-data:www-data /opt/drupal
WORKDIR /var/www/html
RUN composer install
COPY web/sites/default/settings.php /var/www/html/web/sites/default/settings.php
COPY web/sites/default/settings.local.php /var/www/html/web/sites/default/settings.local.php

ENV APACHE_DOCUMENT_ROOT=/var/www/html/web
ENV PHP_INI_PATH /usr/local/etc/php/php.ini
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN cp ${PHP_INI_PATH}-development $PHP_INI_PATH
