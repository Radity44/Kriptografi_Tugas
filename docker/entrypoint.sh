#!/bin/sh
set -e

# Default port ke 80 jika variabel PORT tidak diset oleh platform cloud (seperti Railway)
PORT=${PORT:-80}
echo "Starting Nginx on port: $PORT"

# Ganti port di konfigurasi Nginx sesuai $PORT Railway
sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/http.d/default.conf

# Start php-fpm di background
php-fpm -D

# Jalankan Nginx di foreground
exec nginx -g "daemon off;"

