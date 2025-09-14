# Usa imagem oficial do PHP com Apache
FROM php:8.2-apache

# Instala dependências do Laravel
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl \
    && docker-php-ext-install pdo pdo_mysql zip

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia código para o container
COPY . /var/www/html

WORKDIR /var/www/html

# Instala dependências do Laravel
RUN composer install --optimize-autoloader --no-dev

# Configura permissões
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Configura porta do Cloud Run
ENV PORT 8000
EXPOSE 8000

# Ajusta Apache para ouvir na porta certa
RUN sed -i "s/80/${PORT}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Habilita mod_rewrite para Laravel
RUN a2enmod rewrite

# Start Apache
CMD ["apache2-foreground"]
