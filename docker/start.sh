#!/bin/bash

# Wait for database to be ready (if using external database)
# Uncomment and modify if needed:
# while ! php artisan db:show --quiet; do
#     echo "Waiting for database connection..."
#     sleep 2
# done

# Run migrations
php artisan migrate --force

# Clear and cache config
php artisan config:clear
php artisan config:cache

# Clear and cache routes
php artisan route:clear
php artisan route:cache

# Clear and cache views
php artisan view:clear
php artisan view:cache

# Start Apache
apache2-foreground

