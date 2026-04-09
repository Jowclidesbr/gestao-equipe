<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Fix tmpfile() for XAMPP: Apache's daemon user needs a writable temp dir
if (is_dir('/Applications/XAMPP/xamppfiles/temp')) {
    putenv('TMPDIR=/Applications/XAMPP/xamppfiles/temp');
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
