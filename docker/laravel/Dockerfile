FROM php:8.3-cli-alpine

RUN apk add --no-cache bash curl git icu-dev libzip-dev oniguruma-dev postgresql-dev zip unzip shared-mime-info mailcap \
    && docker-php-ext-install intl mbstring pdo pdo_pgsql zip

RUN touch /usr/local/etc/php/conf.d/uploads.ini \
    && echo "upload_max_filesize = 50M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock* ./
RUN composer install --no-interaction --prefer-dist --no-scripts || true

COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader \
    && chmod +x docker/laravel/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["docker/laravel/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
