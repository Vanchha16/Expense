<?php
/**
 * One-time migration endpoint — protected by MIGRATE_SECRET env var.
 * Call: GET /migrate-run?key=YOUR_SECRET
 */

$secret = getenv('MIGRATE_SECRET') ?: '';
if ($secret === '' || ($_GET['key'] ?? '') !== $secret) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// Create writable storage dirs in /tmp (same as index.php)
foreach ([
    '/tmp/storage/logs',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/bootstrap/cache',
] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0755, true);
}

$_SERVER['HTTPS']       = 'on';
$_SERVER['SERVER_PORT'] = '443';

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->useStoragePath('/tmp/storage');

$kernel   = $app->make(Illuminate\Contracts\Console\Kernel::class);
$exitCode = $kernel->call('migrate', ['--force' => true]);
$output   = $kernel->output();

header('Content-Type: application/json');
echo json_encode([
    'status'    => $exitCode === 0 ? 'success' : 'error',
    'exit_code' => $exitCode,
    'output'    => $output,
]);
