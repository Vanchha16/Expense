#!/bin/bash
set -e

echo "──────────────────────────────────────────────"
echo " ExpenseTrack – container startup"
echo "──────────────────────────────────────────────"

# ── 1. Patch nginx to listen on the platform-injected PORT ───────
# Render injects PORT=10000; Koyeb injects PORT=8000.
# Falls back to 10000 if not set (local Docker testing).
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

# ── 3. Normalise DATABASE_URL for Aiven / Laravel compatibility ──
# Aiven issues postgres:// URIs; Laravel's PDO driver requires postgresql://
if [[ "$DATABASE_URL" == postgres://* ]]; then
    export DATABASE_URL="${DATABASE_URL/postgres:\/\//postgresql://}"
    echo "[boot] DATABASE_URL scheme normalised to postgresql://"
fi

# Inject connect_timeout so cold-start DB handshakes don't hang indefinitely
if [[ -n "$DATABASE_URL" ]] && [[ "$DATABASE_URL" != *"connect_timeout"* ]]; then
    # Append as a query-string parameter (works whether or not there's already a '?')
    if [[ "$DATABASE_URL" == *"?"* ]]; then
        export DATABASE_URL="${DATABASE_URL}&connect_timeout=10"
    else
        export DATABASE_URL="${DATABASE_URL}?connect_timeout=10"
    fi
    echo "[boot] connect_timeout=10 appended to DATABASE_URL"
fi

# ── 4. Validate APP_KEY format ───────────────────────────────────
# Render's generateValue produces a plain string; Laravel needs base64:<32-byte-key>
if [[ -z "$APP_KEY" ]] || [[ "$APP_KEY" != base64:* ]]; then
    echo "[boot] APP_KEY missing or wrong format — generating valid key..."
    export APP_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
    echo "[boot] APP_KEY generated OK"
fi

# ── 5. Laravel production optimisations ──────────────────────────
echo "[boot] Caching config / routes / views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ── 6. Wait for DB with retry loop (handles cold-start latency) ──
MAX_RETRIES=5
RETRY=0
echo "[boot] Waiting for database connection..."
until php artisan db:show > /dev/null 2>&1; do
    RETRY=$((RETRY + 1))
    if [ "$RETRY" -ge "$MAX_RETRIES" ]; then
        echo "[boot] ERROR: database unreachable after ${MAX_RETRIES} attempts — aborting."
        exit 1
    fi
    echo "[boot] DB not ready yet — retry ${RETRY}/${MAX_RETRIES} in 5 s..."
    sleep 5
done
echo "[boot] Database connection OK"

# ── 7. Run any pending database migrations ────────────────────────
# --force bypasses the "are you sure?" prompt in production
echo "[boot] Running database migrations..."
php artisan migrate --force
echo "[boot] Migrations complete"

# ── 8. Hand off to supervisord (nginx + php-fpm) ─────────────────
echo "[boot] Starting nginx + php-fpm via supervisor..."
exec /usr/bin/supervisord -n -c /etc/supervisord.conf
