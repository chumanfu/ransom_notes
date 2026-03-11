<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // IONOS (and some hosts) handle /api/* before PHP; use /v1/* so requests reach Laravel
            Route::prefix('v1')->group(base_path('routes/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (Throwable $e) {
            // Write to public/crash.log so we see errors even when storage/logs isn't writable
            $crashLog = dirname(__DIR__).'/public/crash.log';
            @file_put_contents(
                $crashLog,
                date('c').' '.get_class($e).': '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine().PHP_EOL.$e->getTraceAsString().PHP_EOL,
                FILE_APPEND
            );
        });
    })->create();
