# Gunakan PHP 8.3 CLI Alpine yang ringan dan stabil
FROM php:8.3-cli-alpine

# Install library pendukung sistem untuk PostgreSQL, Zip, dan Curl
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    curl

# Install ekstensi PHP: pdo, pdo_pgsql, pgsql, zip
RUN docker-php-ext-install pdo pdo_pgsql pgsql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Atur direktori kerja aplikasi
WORKDIR /var/www/html

# Salin seluruh source code project
COPY . .

# Install dependencies composer untuk production
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Berikan izin tulis penuh pada folder storage dan cache
RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Port default
EXPOSE 8080

# Multi-worker untuk menangani concurrent request
ENV PHP_CLI_SERVER_WORKERS=4

# Startup script yang membaca $PORT dinamis dari Railway secara langsung
CMD ["sh", "-c", "php artisan config:clear && php artisan route:clear && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
