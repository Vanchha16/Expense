#!/bin/bash
set -e

echo "──────────────────────────────────────────────"
echo " ExpenseTrack – container startup"
echo "──────────────────────────────────────────────"

# ── 1. Patch nginx to listen on Render's injected PORT ────────────
PORT="${PORT:-10000}"
sed -i "s/NGINX_PORT/${PORT}/g" /etc/nginx/conf.d/default.conf
echo "[boot] nginx will listen on port ${PORT}"

# ── 2. Ensure storage directories exist and are writable ─────────
mkdir -p \
    storage/logs \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
echo "[boot] storage permissions set"

# ── 3. Laravel production optimisations ──────────────────────────
echo "[boot] Caching config / routes / views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ── 4. Run any pending database migrations ────────────────────────
# --force bypasses the "are you sure?" prompt in production
echo "[boot] Running database migrations..."
php artisan migrate --force
echo "[boot] Migrations complete"

# ── 5. Hand off to supervisord (nginx + php-fpm) ─────────────────
echo "[boot] Starting nginx + php-fpm via supervisor..."
exec /usr/bin/supervisord -n -c /etc/supervisord.conf
