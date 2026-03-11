<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (Throwable $e) {
            $logDir = storage_path('logs');
            $crashLog = $logDir.DIRECTORY_SEPARATOR.'crash.log';
            if (is_writable($logDir) || (@mkdir($logDir, 0777, true) && is_writable($logDir))) {
                @file_put_contents(
                    $crashLog,
                    date('c').' '.get_class($e).': '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine().PHP_EOL.$e->getTraceAsString().PHP_EOL,
                    FILE_APPEND
                );
            }
        });
    })->create();
