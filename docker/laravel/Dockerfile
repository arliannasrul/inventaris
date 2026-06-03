FROM php:8.3-cli-alpine

RUN apk add --no-cache bash curl git icu-dev libzip-dev oniguruma-dev postgresql-dev zip unzip \
    && docker-php-ext-install intl mbstring pdo pdo_pgsql zip

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
