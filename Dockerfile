FROM php:8.2-apache

WORKDIR /var/www/html/

RUN apt-get update \
    && apt-get install -y zip git unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd zip

RUN curl -sS https://getcomposer.org/installer | php -- \
        --filename=composer \
        --install-dir=/usr/local/bin \
    && composer clear-cache

RUN chmod 755 \
    /usr/local/bin/docker-php-entrypoint \
    /usr/local/bin/composer

COPY . .

EXPOSE 8080

CMD composer install \
    && ls -la && cat start.sh && chmod +x /var/www/html/start.sh && /var/www/html/start.sh
