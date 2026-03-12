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
        $crashLog = dirname(__DIR__).'/public/crash.log';
        $exceptions->report(function (Throwable $e) use ($crashLog) {
            @file_put_contents(
                $crashLog,
                date('c').' '.get_class($e).': '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine().PHP_EOL.$e->getTraceAsString().PHP_EOL,
                FILE_APPEND
            );
        });
        // Return readable HTML for 500s so we see the error on the server (not just "Internal Server Error")
        $exceptions->render(function (Throwable $e, $request) use ($crashLog) {
            if (! $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $showError = config('app.debug');
                $msg = $showError
                    ? htmlspecialchars($e->getMessage().' in '.$e->getFile().':'.$e->getLine())
                    : 'Server error. Check public/crash.log or set APP_DEBUG=true in .env.';
                return response(
                    '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Error</title></head><body style="font-family:sans-serif;max-width:700px;margin:2rem auto;padding:1rem;"><h1>Application Error</h1><pre style="white-space:pre-wrap;background:#f5f5f5;padding:1rem;overflow:auto;">'.($showError ? $msg."\n\n".htmlspecialchars($e->getTraceAsString()) : $msg).'</pre></body></html>',
                    500,
                    ['Content-Type' => 'text/html; charset=utf-8']
                );
            }
        });
    })->create();
