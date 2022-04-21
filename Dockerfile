FROM drupal:latest

COPY composer.json /opt/drupal
COPY db.sql /opt/drupal
COPY composer.lock /opt/drupal
COPY vendor /opt/drupal
COPY web /opt/drupal
COPY load.environment.php /opt/drupal
COPY inline-images/ /opt/drupal/web/sites/default/files/

# RUN chown -R www-data:www-data /opt/drupal
RUN chown -R www-data:www-data /var/www
WORKDIR /var/www/html
RUN chmod 777 -R /var/www
RUN apache2ctl restart
EXPOSE 80
