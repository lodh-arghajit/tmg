FROM devwithlando/php:7.4-apache-2

COPY composer.json /var/www/html
COPY db.sql /var/www/html
COPY composer.lock /var/www/html
COPY vendor /var/www/html/vendor
COPY web /var/www/html/web
COPY load.environment.php /var/www/html


# RUN chown -R www-data:www-data /opt/drupal
WORKDIR /var/www/html
ENV APACHE_DOCUMENT_ROOT=/var/www/html/web
ENV PHP_INI_PATH /usr/local/etc/php/php.ini
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN cp ${PHP_INI_PATH}-development $PHP_INI_PATH
