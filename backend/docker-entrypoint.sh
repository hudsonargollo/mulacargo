#!/bin/sh
set -e

echo "Starting MulaCargo Backend Container..."

# Ensure storage directories exist and have proper permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Start PHP-FPM daemon
php-fpm -D

# Start Nginx in foreground
echo "MulaCargo Backend Engine Online."
exec nginx -g "daemon off;"
