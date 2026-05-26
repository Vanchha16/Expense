<?php
/**
 * Vercel PHP serverless entry point for Laravel.
 *
 * Vercel's filesystem is read-only except /tmp.
 * We create the directories Laravel needs in /tmp and redirect
 * storage there before the application boots.
 */

use Illuminate\Http\Request;

// ── 1. Create writable directories in /tmp ────────────────────────
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

// ── 2. Tell Laravel we're behind Vercel's HTTPS edge ─────────────
$_SERVER['HTTPS']       = 'on';
$_SERVER['SERVER_PORT'] = '443';

// ── 3. Boot Laravel ───────────────────────────────────────────────
define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// ── 4. Redirect all file I/O to /tmp (writable on Vercel) ─────────
$app->useStoragePath('/tmp/storage');

// ── 5. Dispatch the HTTP request ──────────────────────────────────
$app->handleRequest(Request::capture());
