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

@unlink(__DIR__ . '/../bootstrap/cache/services.php');

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

try {
    $app->handleRequest(Illuminate\Http\Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    $trace = array_slice(array_map(function($t) {
        return ($t['file'] ?? '?') . ':' . ($t['line'] ?? '?') . ' ' . ($t['function'] ?? '');
    }, $e->getTrace()), 0, 8);
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $trace,
    ]);
}
