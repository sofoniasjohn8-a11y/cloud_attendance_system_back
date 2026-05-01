<?php

define('LARAVEL_START', microtime(true));

// Vercel has a read-only filesystem, use /tmp for writable paths
$storageDir = '/tmp/storage';
foreach ([
    "$storageDir/framework/cache/data",
    "$storageDir/framework/sessions",
    "$storageDir/framework/views",
    "$storageDir/logs",
    "$storageDir/app",
    '/tmp/bootstrap/cache',
] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0775, true);
}

// Copy bootstrap cache to writable /tmp location so Laravel can regenerate packages.php
// without dev dependencies (which aren't installed on Vercel)
$srcCache = __DIR__ . '/../bootstrap/cache';
$dstCache = '/tmp/bootstrap/cache';
foreach (glob("$srcCache/*.php") as $file) {
    $dest = "$dstCache/" . basename($file);
    if (!file_exists($dest)) copy($file, $dest);
}
// Always delete packages.php so it gets regenerated without dev packages
@unlink("$dstCache/packages.php");

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath($storageDir);
$app->bootstrapPath('/tmp/bootstrap');

$app->handleRequest(Illuminate\Http\Request::capture());
