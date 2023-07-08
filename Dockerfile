FROM php:7.4-apache

WORKDIR /var/www/html/

# Install Postgres
RUN apt-get update \
    && apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

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

EXPOSE 8080

CMD apt-get update && apt-get -y install zip && apt-get -y install git && composer install \
  && ls -la && cat start.sh && chmod +x /var/www/html/start.sh && /var/www/html/start.sh