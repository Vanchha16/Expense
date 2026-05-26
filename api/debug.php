<?php
// Temporary debug endpoint — remove after fixing
header('Content-Type: application/json');

$info = [
    'php_version'    => PHP_VERSION,
    'extensions'     => get_loaded_extensions(),
    'pdo_drivers'    => PDO::getAvailableDrivers(),
    'tmp_writable'   => is_writable('/tmp'),
    'storage_exists' => is_dir(__DIR__ . '/../storage'),
    'vendor_exists'  => file_exists(__DIR__ . '/../vendor/autoload.php'),
    'env_db_url_set' => !empty(getenv('DATABASE_URL')),
    'env_app_key'    => substr(getenv('APP_KEY') ?: '', 0, 10) . '...',
];

// Test DB connection
try {
    $dsn = getenv('DATABASE_URL') ?: '';
    // Normalise postgres:// → postgresql://
    $dsn = str_replace('postgres://', 'postgresql://', $dsn);
    $pdo = new PDO($dsn);
    $info['db_connect'] = 'OK';
    $info['db_version'] = $pdo->query('SELECT version()')->fetchColumn();
} catch (Throwable $e) {
    $info['db_connect'] = 'FAILED';
    $info['db_error']   = $e->getMessage();
}

echo json_encode($info, JSON_PRETTY_PRINT);
