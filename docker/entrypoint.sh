#!/bin/sh
set -e

# Ensure storage directories exist (required when storage/ is a fresh volume)
mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions storage/logs bootstrap/cache

# Fix ownership so PHP-FPM (www-data) can write logs, cache, sessions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Cache Laravel configuration, routes and compiled views for production
php artisan config:cache
php artisan route:cache
php artisan view:cache 2>/dev/null || true

# Run pending database migrations automatically on startup
php artisan migrate --force --no-interaction

# Hand off to Supervisor, which manages PHP-FPM and Nginx
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
