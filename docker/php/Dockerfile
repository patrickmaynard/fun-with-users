FROM php:8.0-fpm

RUN apt-get update && \
    apt-get install -y \
        zip \
        unzip \
        netcat && \
    apt-get clean && \
    pecl install \
        xdebug-3.0.2 && \
    docker-php-ext-enable \
        xdebug && \
    docker-php-ext-install \
        pdo \
        pdo_mysql

RUN curl --silent --show-error https://getcomposer.org/installer | php -- \
        --version=2.0.8 \
        --install-dir=/usr/local/bin \
        --filename=composer

RUN echo 'xdebug.client_port=9003' >> /usr/local/etc/php/php.ini
RUN echo 'xdebug.mode=debug' >> /usr/local/etc/php/php.ini
RUN echo 'xdebug.client_host=host.docker.internal' >> /usr/local/etc/php/php.ini

CMD ["php-fpm"]
