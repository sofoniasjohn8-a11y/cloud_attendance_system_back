<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

try {
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

    echo "step1:storage_ok ";

    require __DIR__ . '/../vendor/autoload.php';
    echo "step2:autoload_ok ";

    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "step3:app_ok ";

    $app->useStoragePath($storageDir);
    echo "step4:storage_set ";

    $app->handleRequest(Illuminate\Http\Request::capture());

} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
}
