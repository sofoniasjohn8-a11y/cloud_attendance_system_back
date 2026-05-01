<?php

define('LARAVEL_START', microtime(true));

// Vercel has a read-only filesystem, use /tmp for writable storage
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

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath($storageDir);

$app->handleRequest(Illuminate\Http\Request::capture());
