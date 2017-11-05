FROM php:7.1-fpm

RUN apt-get update && apt-get install -y wget vim git bash-completion zlib1g-dev libicu-dev g++ libssl-dev libxml2-dev libfreetype6-dev libjpeg62-turbo-dev libmcrypt-dev libpng12-dev libsasl2-dev libcurl4-openssl-dev pkg-config --fix-missing

RUN docker-php-ext-configure intl
RUN docker-php-ext-install iconv intl mbstring mcrypt opcache pdo pdo_mysql

RUN curl -LsS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

RUN composer global require sllh/composer-versions-check

RUN curl -LsS https://phar.phpunit.de/phpunit.phar -o /usr/local/bin/phpunit
RUN chmod a+x /usr/local/bin/phpunit

ADD application.ini /usr/local/etc/php/conf.d/application.ini

ENTRYPOINT sh /var/www/html/docker/build/php/entrypoint.sh && php-fpm

ADD .bashrc /var/www/.bashrc

RUN usermod -s /bin/bash www-data
RUN usermod -u 1000 www-data
RUN chown -R 1000 /var/www/

RUN echo "export TERM=xterm" >> /etc/bash.bashrc
