FROM php:8.2-fpm

# Dependências
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl libpng-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql zip gd

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia projeto
COPY . .

# Instala dependências
RUN composer install --optimize-autoloader --no-dev

# Permissões para Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
