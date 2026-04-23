# ─────────────────────────────────────────────────────────────
# Stage 1: Frontend assets (Vite build)
# ─────────────────────────────────────────────────────────────
FROM node:20-slim AS node_builder
WORKDIR /app

COPY --link package.json package-lock.json ./
RUN npm ci --prefer-offline

COPY --link . .
RUN npm run build

# ─────────────────────────────────────────────────────────────
# Stage 2: PHP vendor dependencies (Composer)
# ─────────────────────────────────────────────────────────────
FROM composer:2 AS vendor_builder
WORKDIR /app

COPY --link composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

COPY --link . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

# ─────────────────────────────────────────────────────────────
# Stage 3: Runtime (PHP-FPM + Nginx in the same container)
# ─────────────────────────────────────────────────────────────
FROM php:8.4-fpm-bookworm AS runtime

# System packages: Nginx, Supervisor, PHP extension deps, Chromium (for Browsershot)
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        # Headless Chrome / Browsershot
        chromium \
        libnss3 \
        libatk-bridge2.0-0 \
        libgbm1 \
        libasound2 \
        fonts-noto \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        pdo_mysql \
        zip \
        bcmath \
        gd \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis opcache \
    && rm -rf /tmp/pear

WORKDIR /var/www/html

# Application code (vendor/ and public/build/ excluded via .dockerignore)
COPY --link --chown=www-data:www-data . .

# Overlay with build-stage artifacts
COPY --link --chown=www-data:www-data --from=vendor_builder /app/vendor        ./vendor
COPY --link --chown=www-data:www-data --from=node_builder   /app/public/build  ./public/build

# Infrastructure config
COPY --link docker/nginx/nginx.conf          /etc/nginx/nginx.conf
COPY --link docker/nginx/default.conf        /etc/nginx/sites-available/default
COPY --link docker/php/php.ini               /usr/local/etc/php/conf.d/99-app.ini
COPY --link docker/php/www.conf              /usr/local/etc/php-fpm.d/www.conf
COPY --link docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY --link docker/entrypoint.sh             /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Writable dirs with correct ownership — no recursive chown
RUN install -d -o www-data -g www-data -m 775 \
        storage/logs \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache

# ── ARGs last: changing these does not invalidate dependency cache layers ──
ARG APP_VERSION=development
ARG APP_VERSION_LABEL=development
ARG APP_VERSION_TYPE=dev

ENV APP_VERSION=${APP_VERSION} \
    APP_VERSION_LABEL=${APP_VERSION_LABEL} \
    APP_VERSION_TYPE=${APP_VERSION_TYPE} \
    CHROMIUM_PATH=/usr/bin/chromium

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
