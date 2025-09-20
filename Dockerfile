FROM php:8.2-fpm

# Dependências do Laravel + PostgreSQL + Nginx
RUN apt-get update && apt-get install -y \
    nginx libpq-dev zip unzip git curl libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring bcmath gd zip opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --optimize-autoloader --no-dev \
    && chown -R www-data:www-data storage bootstrap/cache

# Nginx
RUN rm /etc/nginx/sites-enabled/default
COPY default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

# Rodando PHP-FPM + Nginx no mesmo container
CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
