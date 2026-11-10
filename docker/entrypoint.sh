#!/bin/sh
set -e

# Default port ke 80 jika variabel PORT tidak diset oleh platform cloud (seperti Railway)
PORT=${PORT:-80}
echo "Starting application on PORT: $PORT"

# Ganti port di konfigurasi Nginx sesuai $PORT Railway
sed -i "s/listen 80 default_server;/listen ${PORT} default_server;/g" /etc/nginx/http.d/default.conf
sed -i "s/listen \[::\]:80 default_server;/listen \[::\]:${PORT} default_server;/g" /etc/nginx/http.d/default.conf

# Bersihkan config cache agar env Railway terbaca
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Test konfigurasi Nginx
nginx -t

# Start php-fpm di background
php-fpm -D

# Jalankan Nginx di foreground
exec nginx -g "daemon off;"
