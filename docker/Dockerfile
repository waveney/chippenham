FROM php:8.2-apache

ARG UID
ARG GID

ENV UID=${UID}
ENV GID=${GID}

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y libmagickwand-dev wkhtmltopdf ssl-cert
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN docker-php-ext-install gd && docker-php-ext-enable gd
RUN pecl install -o -f imagick &&  docker-php-ext-enable imagick

RUN a2enmod rewrite
RUN a2enmod ssl

RUN a2ensite default-ssl.conf

RUN ln -s /usr/bin/wkhtmltopdf /usr/local/bin/html2pdf

RUN curl -LO https://github.com/skeema/skeema/releases/download/v1.10.1/skeema_1.10.1_linux_amd64.tar.gz
RUN tar -xzvf skeema_1.10.1_linux_amd64.tar.gz skeema
RUN mv skeema /usr/bin/
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini && \
    sed -i 's/error_reporting = E_ALL/error_reporting = E_ALL \& ~E_DEPRECATED/g' /usr/local/etc/php/php.ini
RUN echo 'include_path = ".:/var/www/html"' >> /usr/local/etc/php/php.ini
