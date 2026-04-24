# ─────────────────────────────────────────────────────────────
# Stage 1: Frontend assets (Vite build)
# ─────────────────────────────────────────────────────────────
FROM node:22-slim AS node_builder
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
        mbstring \
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
# --chown is incompatible with --link (BuildKit limitation); ownership set below
COPY --link . .

# Overlay with build-stage artifacts
COPY --link --from=vendor_builder /app/vendor       ./vendor
COPY --link --from=node_builder   /app/public/build ./public/build

# Infrastructure config
COPY --link docker/nginx/nginx.conf            /etc/nginx/nginx.conf
COPY --link docker/nginx/default.conf          /etc/nginx/sites-available/default
COPY --link docker/php/php.ini                 /usr/local/etc/php/conf.d/99-app.ini
COPY --link docker/php/www.conf                /usr/local/etc/php-fpm.d/www.conf
COPY --link docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY --link docker/entrypoint.sh               /entrypoint.sh

# Set ownership and permissions in a single layer
RUN chown -R www-data:www-data /var/www/html \
    && sed -i 's/\r$//' /entrypoint.sh \
    && chmod +x /entrypoint.sh \
    && chmod -R 775 storage bootstrap/cache

# ── Version info (ARGs last: changing these does not bust dependency cache) ──
ARG APP_VERSION=development
ARG APP_VERSION_LABEL=development
ARG APP_VERSION_TYPE=dev
ARG APP_REF_URL=
ARG APP_COMMIT_SHA=unknown

ENV APP_VERSION=${APP_VERSION} \
    APP_VERSION_LABEL=${APP_VERSION_LABEL} \
    APP_VERSION_TYPE=${APP_VERSION_TYPE} \
    APP_REF_URL=${APP_REF_URL} \
    APP_COMMIT_SHA=${APP_COMMIT_SHA} \
    CHROMIUM_PATH=/usr/bin/chromium

RUN printf '%s\n' "$APP_VERSION" > /var/www/html/VERSION \
    && php -r ' \
        $d = [ \
            "version"       => getenv("APP_VERSION")       ?: "development", \
            "version_type"  => getenv("APP_VERSION_TYPE")  ?: "development", \
            "version_label" => getenv("APP_VERSION_LABEL") ?: "development", \
            "commit"        => getenv("APP_COMMIT_SHA")    ?: "unknown", \
            "ref_url"       => getenv("APP_REF_URL")       ?: "", \
        ]; \
        file_put_contents( \
            "/var/www/html/VERSION_INFO.json", \
            json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) \
        );'

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
