# ─────────────────────────────────────────────────────────────────
# Stage 1 – Dependency installer (keeps vendor layer cacheable)
# ─────────────────────────────────────────────────────────────────
FROM composer:2 AS deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction

# ─────────────────────────────────────────────────────────────────
# Stage 2 – Production image
# ─────────────────────────────────────────────────────────────────
FROM php:8.2-fpm-alpine

# ── System packages ───────────────────────────────────────────────
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    libzip-dev \
    oniguruma-dev \
    curl \
    bash

# ── PHP extensions ────────────────────────────────────────────────
RUN docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        zip \
        mbstring \
        bcmath \
        opcache \
        pcntl

# ── Copy vendor from installer stage ─────────────────────────────
WORKDIR /var/www/html
COPY --from=deps /app/vendor ./vendor

# ── Application source ────────────────────────────────────────────
COPY . .

# ── Optimised classmap (no dev deps, no scripts yet) ─────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev \
    && composer run-script post-autoload-dump --no-interaction 2>/dev/null || true

# ── Writable directories ──────────────────────────────────────────
RUN mkdir -p \
        storage/logs \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/cache/data \
        bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# ── Docker config files ───────────────────────────────────────────
COPY docker/php.ini          /usr/local/etc/php/conf.d/production.ini
COPY docker/nginx.conf       /etc/nginx/nginx.conf
COPY docker/nginx-site.conf  /etc/nginx/conf.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh    /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Render injects PORT=10000 by default
EXPOSE 10000

ENTRYPOINT ["/entrypoint.sh"]
