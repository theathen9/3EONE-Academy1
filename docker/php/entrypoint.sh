#!/bin/sh
set -e

# Execute tasks specific to the 'app' container
if [ "$CONTAINER_ROLE" = "app" ]; then
    echo "Running production optimizations..."
    
    # Run migrations (force flag required in production)
    php artisan migrate --force

    # Cache configurations, routes, events, and views
    php artisan config:cache
    php artisan event:cache
    php artisan route:cache
    php artisan view:cache

    # Ensure storage symlink exists
    php artisan storage:link || true
fi

# Execute the primary container command (e.g. php-fpm, queue worker, scheduler)
exec "$@"