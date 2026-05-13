#!/bin/sh
set -e

# Ensure storage directories exist (required on first run with a fresh volume)
mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions storage/logs bootstrap/cache

# Fix ownership so PHP-FPM (www-data) can write to storage on every restart
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

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

# Create test database if it doesn't exist (used by php artisan test)
TEST_DB="${DB_DATABASE:-pnte}_test"
PGPASSWORD="${DB_PASSWORD}" psql -h "${DB_HOST:-db}" -U "${DB_USERNAME:-pnte}" -d postgres \
    -tc "SELECT 1 FROM pg_database WHERE datname = '${TEST_DB}'" \
    | grep -q 1 || {
  PGPASSWORD="${DB_PASSWORD}" psql -h "${DB_HOST:-db}" -U "${DB_USERNAME:-pnte}" -d postgres \
    -c "CREATE DATABASE ${TEST_DB} WITH TEMPLATE template0 ENCODING 'UTF8';" && \
  PGPASSWORD="${DB_PASSWORD}" psql -h "${DB_HOST:-db}" -U "${DB_USERNAME:-pnte}" -d "${TEST_DB}" \
    -c "CREATE EXTENSION IF NOT EXISTS postgis; CREATE EXTENSION IF NOT EXISTS postgis_tiger_geocoder CASCADE; CREATE EXTENSION IF NOT EXISTS postgis_topology;" || true
}

# Run pending database migrations
php artisan migrate --force --no-interaction

exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
