<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// 1. HAPUS ATAU KOMENTARI pengecekan maintenance mode berbasis file lokal ini
// if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
//     require $maintenance;
// }

// 2. Pastikan pemanggilan autoload dan bootstrap keluar 1 tingkat folder ('/../') dengan benar
require __DIR__ . '/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->handleRequest(Request::capture());
