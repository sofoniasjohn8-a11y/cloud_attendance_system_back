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

require __DIR__ . '/../vendor/autoload.php';

// Patch the Application class to set storage path before providers boot
Illuminate\Foundation\Application::macro('storagePath', function ($path = '') use ($storageDir) {
    return $storageDir . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
});

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
