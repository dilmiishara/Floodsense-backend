FROM php:8.2-fpm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD bash -c "\
    echo \$FIREBASE_CREDENTIALS_BASE64 | base64 -d > storage/app/firebase-credentials.json && \
    php artisan config:clear && \
    php artisan route:cache && \
    php artisan serve --host=0.0.0.0 --port=8080 \
"
