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

require __DIR__ . '/../vendor/autoload.php';

// Remove dev-only packages from packages.php at runtime
$pkgFile = __DIR__ . '/../bootstrap/cache/packages.php';
if (file_exists($pkgFile)) {
    $devOnly = ['laravel/boost', 'laravel/mcp', 'laravel/pail', 'laravel/roster', 'laravel/sail', 'nunomaduro/collision', 'pestphp/pest-plugin-laravel'];
    $packages = require $pkgFile;
    $changed = false;
    foreach ($devOnly as $pkg) {
        if (isset($packages[$pkg])) {
            unset($packages[$pkg]);
            $changed = true;
        }
    }
    if ($changed) {
        $tmpPkg = '/tmp/packages.php';
        file_put_contents($tmpPkg, '<?php return ' . var_export($packages, true) . ';');
        // Symlink or copy to override
        @unlink($pkgFile);
        copy($tmpPkg, $pkgFile);
    }
}

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
