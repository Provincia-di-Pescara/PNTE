#!/bin/sh
set -e

# Rimuove cache di configurazione/route eventualmente prodotta dall'immagine prod,
# così le env var del container dev hanno sempre la precedenza.
php artisan config:clear 2>/dev/null || true
php artisan route:clear  2>/dev/null || true

# Install PHP dependencies (including dev) if vendor/ is not yet present.
# On first run the mounted code directory has no vendor/; composer writes it
# to the host filesystem so subsequent startups are fast.
if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

# Build frontend assets if Vite manifest is missing.
# Required because views use @vite() which reads public/build/manifest.json.
if [ ! -f public/build/manifest.json ]; then
    npm ci --prefer-offline 2>/dev/null || npm install
    npm run build
fi

# Run pending database migrations
php artisan migrate --force --no-interaction

exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
