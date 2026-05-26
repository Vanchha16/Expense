<?php
/**
 * One-time migration endpoint — protected by MIGRATE_SECRET env var.
 * Call: GET /migrate-run?key=YOUR_SECRET
 * Delete this file after migrations have been run.
 */
$secret = getenv('MIGRATE_SECRET') ?: '';
if ($secret === '' || ($_GET['key'] ?? '') !== $secret) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden — set MIGRATE_SECRET env var and pass ?key=VALUE']);
    exit;
}

$_SERVER['HTTPS']       = 'on';
$_SERVER['SERVER_PORT'] = '443';

// Bootstrap Laravel
chdir(__DIR__ . '/..');
require __DIR__ . '/../vendor/autoload.php';

$app    = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$exitCode = $kernel->call('migrate', ['--force' => true]);
$output   = $kernel->output();

header('Content-Type: application/json');
echo json_encode([
    'status'    => $exitCode === 0 ? 'success' : 'error',
    'exit_code' => $exitCode,
    'output'    => $output,
]);
