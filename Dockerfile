FROM drupal:latest

COPY composer.json /var/www/html
COPY db.sql /var/www/html
COPY composer.lock /var/www/html
COPY vendor /var/www/html/vendor
COPY web /var/www/html/web
COPY load.environment.php /var/www/html
COPY inline-images/ /var/www/html/web/sites/default/files/

# RUN chown -R www-data:www-data /opt/drupal
RUN chown -R www-data:www-data /var/www
WORKDIR /var/www/html
RUN chmod 777 -R /var/www
RUN apache2ctl restart
EXPOSE 80
