#!/bin/sh
# ./docker/nginx/entrypoint.sh
set -e

# Run caching only if in production
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan event:cache
    php artisan route:cache
    php artisan view:cache
fi

exec "$@"