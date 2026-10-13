
FROM php:8.2-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev libonig-dev \
    && docker-php-ext-install curl mbstring fileinfo \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . .

COPY php.ini /usr/local/etc/php/conf.d/app.ini

EXPOSE 10000

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} -t /var/www/html"]
