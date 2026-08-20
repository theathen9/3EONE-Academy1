#!/bin/sh
# ./docker/php/entrypoint.sh
set -e

echo "Starting Laravel..."

# if [ "$CONTAINER_ROLE" = "app" ]; then
    php artisan config:cache
    php artisan event:cache
    php artisan route:cache
    php artisan view:cache
# fi

exec "$@"
