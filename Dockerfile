FROM php:7.4-apache

WORKDIR /var/www/html/

RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=Composer

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- \
        --filename=composer \
        --install-dir=/usr/local/bin \
    && composer clear-cache

# Permission adjustment
RUN chmod 755 \
    /usr/local/bin/docker-php-entrypoint \
    /usr/local/bin/composer

COPY . .

RUN apt-get update
RUN apt-get -y install zip
RUN apt-get -y install git
RUN composer install
