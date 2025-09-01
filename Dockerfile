FROM php:8.4-fpm-alpine

RUN apk add --no-cache git curl libpng-dev zip unzip postgresql-dev redis
RUN docker-php-ext-install pdo pdo_pgsql bcmath gd sockets
RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --optimize-autoloader
RUN composer require laravel/octane spiral/roadrunner

EXPOSE 8000
CMD ["php", "artisan", "octane:start", "--server=roadrunner", "--host=0.0.0.0", "--port=8000"]