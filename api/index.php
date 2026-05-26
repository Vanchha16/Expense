<?php
/**
 * Vercel PHP serverless entry point for Laravel.
 *
 * Two problems solved here:
 *
 * 1. Read-only filesystem  — Vercel's /var/task is read-only.
 *    We create the directories Laravel needs in /tmp and redirect
 *    storage + bootstrap cache there before the app boots.
 *
 * 2. Stale services.php cache — The build pipeline commits/caches
 *    a services.php that references dev-only providers (Pail, Sail).
 *    We point APP_SERVICES_CACHE at /tmp so Laravel finds no file,
 *    runs fresh package discovery, and only loads production packages.
 */

use Illuminate\Http\Request;

// ── 1. Writable directories in /tmp ──────────────────────────────
foreach ([
    '/tmp/storage/logs',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/bootstrap/cache',
] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// ── 2. Redirect bootstrap cache to /tmp ──────────────────────────
// Laravel reads APP_SERVICES_CACHE / APP_PACKAGES_CACHE env vars
// to locate these files. Pointing them at /tmp where nothing exists
// forces fresh package discovery every cold-start — no stale cache.
putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes-v7.php');
putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');

// ── 3. Tell Laravel we're behind Vercel's HTTPS edge ─────────────
$_SERVER['HTTPS']       = 'on';
$_SERVER['SERVER_PORT'] = '443';

// ── 4. Boot Laravel ───────────────────────────────────────────────
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// ── 5. Redirect file I/O to /tmp ─────────────────────────────────
$app->useStoragePath('/tmp/storage');

// ── 6. Dispatch the request ───────────────────────────────────────
$app->handleRequest(Request::capture());
