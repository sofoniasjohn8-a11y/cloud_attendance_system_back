<?php

define('LARAVEL_START', microtime(true));

$storageDir = '/tmp/storage';
foreach ([
    "$storageDir/framework/cache/data",
    "$storageDir/framework/sessions",
    "$storageDir/framework/views",
    "$storageDir/logs",
    "$storageDir/app",
] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0775, true);
}

putenv("LARAVEL_STORAGE_PATH=$storageDir");
$_ENV['LARAVEL_STORAGE_PATH'] = $storageDir;
$_SERVER['LARAVEL_STORAGE_PATH'] = $storageDir;

// Check for services.php cache
$servicesCache = __DIR__ . '/../bootstrap/cache/services.php';
if (file_exists($servicesCache)) {
    // Delete it so Laravel re-discovers providers fresh
    @unlink($servicesCache);
}

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

try {
    $app->handleRequest(Illuminate\Http\Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => $e->getMessage(),
        'file'  => str_replace('/var/task/user/', '', $e->getFile()),
        'line'  => $e->getLine(),
    ]);
}
