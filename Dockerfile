# Gunakan PHP 8.3 FPM Alpine
FROM php:8.3-fpm-alpine

# Install ekstensi sistem yang dibutuhkan
RUN apk add --no-cache \
    nginx \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    curl

# Install ekstensi PHP: pdo_pgsql, pgsql, zip
RUN docker-php-ext-install pdo pdo_pgsql pgsql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Atur working directory
WORKDIR /var/www/html

# Salin seluruh kode project
COPY . .

# Install dependencies PHP tanpa dev
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permission folder storage dan bootstrap cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Salin konfigurasi Nginx sederhana
RUN mkdir -p /run/nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Berikan izin eksekusi entrypoint
RUN chmod +x /var/www/html/docker/entrypoint.sh

# Expose port (kompatibel Railway & Render)
EXPOSE 80 8080 3000

# Script startup dinamis untuk port Railway
CMD ["/var/www/html/docker/entrypoint.sh"]
