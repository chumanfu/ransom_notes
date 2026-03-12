<?php
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Capture fatal errors to storage/logs/crash.log (create dir if needed; more likely writable than public/)
$crashLog = __DIR__.'/../storage/logs/crash.log';
register_shutdown_function(function () use ($crashLog) {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $dir = dirname($crashLog);
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($crashLog, date('c').' SHUTDOWN: '.$err['message'].' in '.$err['file'].' on line '.$err['line'].PHP_EOL, FILE_APPEND);
    }
});

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
