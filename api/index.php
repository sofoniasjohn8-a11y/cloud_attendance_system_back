<?php

define('LARAVEL_START', microtime(true));

// Fix storage path for Vercel read-only filesystem
$storageDir = '/tmp/storage';
$dirs = [
    "$storageDir/framework/cache/data",
    "$storageDir/framework/sessions",
    "$storageDir/framework/views",
    "$storageDir/logs",
    "$storageDir/app/public",
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0775, true);
}

// Copy bootstrap cache to writable location
$bootstrapCache = '/tmp/bootstrap/cache';
if (!is_dir($bootstrapCache)) mkdir($bootstrapCache, 0775, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath($storageDir);
$app->bootstrapPath('/tmp/bootstrap');

$app->handleRequest(Illuminate\Http\Request::capture());
