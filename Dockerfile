FROM tehraven/alpinewebos:latest
MAINTAINER "https://github.com/tehraven"
# BUILDS binarygod/AmSYS

ENV BUILD_VERSION=1.0.1
ENV BUILD_ENV=live

WORKDIR /var/www

COPY composer.json /var/www/composer.json
COPY app /var/www/app
COPY src /var/www/src
COPY web /var/www/web
COPY app/config/parameters.yml.dist /var/www/app/config/parameters.yml
RUN chown -R www-data:www-data /var/www/

RUN composer install

RUN php app/console doctrine:schema:update --force
RUN php app/console amsys:settings:populate
RUN php app/console amsys:sde:update
RUN php app/console amsys:cache:update

ADD docker/servers/web/root /

WORKDIR /var/www/
EXPOSE 80