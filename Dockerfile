FROM drupal:latest

COPY vendor/ /var/www/vendor/
COPY web/ /var/www/html


RUN chown -R www-data:www-data /var/www
WORKDIR /var/www/html
RUN chmod 777 -R /var/www
RUN apache2ctl restart
EXPOSE 80
