FROM php:8.2-fpm-alpine

# Installer les extensions PHP nécessaires
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de dépendances
COPY composer.json composer.lock ./

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copier le reste du code
COPY . .

# Exécuter les scripts post-install
RUN composer run-script post-autoload-dump

# Permissions pour Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exposer le port
EXPOSE 8000

# Commande pour démarrer
CMD if [ ! -d "vendor" ]; then composer install --no-dev --optimize-autoloader; fi && php artisan serve --host=0.0.0.0 --port=8000