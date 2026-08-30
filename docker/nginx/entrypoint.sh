#!/bin/sh
# ./docker/nginx/entrypoint.sh
set -e

echo "Optimizing Laravel performance..."

if [ "$CONTAINER_ROLE" = "app" ]; then
    php artisan optimize:clear
    php artisan config:cache
    php artisan event:cache
    php artisan route:cache
    php artisan view:cache
fi

echo "Starting PHP-FPM..."

exec php-fpm -F
