<?php

// Vercel Functions have a read-only application filesystem; keep Laravel's
// runtime storage in the writable temporary directory.
$storagePath = sys_get_temp_dir().'/vehicle-booking';

foreach ([
    'app/private',
    'app/public',
    'framework/cache/data',
    'framework/cache/locks',
    'framework/sessions',
    'framework/views',
    'logs',
] as $directory) {
    $path = $storagePath.'/'.$directory;

    if (! is_dir($path)) {
        mkdir($path, 0775, true);
    }
}

$_SERVER['LARAVEL_STORAGE_PATH'] = $_ENV['LARAVEL_STORAGE_PATH'] = $storagePath;
$_SERVER['VIEW_COMPILED_PATH'] = $_ENV['VIEW_COMPILED_PATH'] = $storagePath.'/framework/views';
$_SERVER['CACHE_STORE'] = $_ENV['CACHE_STORE'] = $_SERVER['CACHE_STORE'] ?? $_ENV['CACHE_STORE'] ?? 'array';
$_SERVER['SESSION_DRIVER'] = $_ENV['SESSION_DRIVER'] = $_SERVER['SESSION_DRIVER'] ?? $_ENV['SESSION_DRIVER'] ?? 'cookie';
$_SERVER['LOG_CHANNEL'] = $_ENV['LOG_CHANNEL'] = $_SERVER['LOG_CHANNEL'] ?? $_ENV['LOG_CHANNEL'] ?? 'stderr';

require __DIR__.'/../public/index.php';
