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
        // So Sanctum sees the token when Apache passes it via REDIRECT_HTTP_AUTHORIZATION
        $middleware->api(prepend: [
            \App\Http\Middleware\AddAuthorizationHeader::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (Throwable $e) {
            $logDir = dirname(__DIR__).'/storage/logs';
            if (! is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $crashLog = $logDir.'/crash.log';
            @file_put_contents(
                $crashLog,
                date('c').' '.get_class($e).': '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine().PHP_EOL.$e->getTraceAsString().PHP_EOL,
                FILE_APPEND
            );
        });
        // Return readable HTML for 500s so we see the error on the server (not just "Internal Server Error")
        $exceptions->render(function (Throwable $e, $request) {
            if (! $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $showError = config('app.debug');
                $msg = $showError
                    ? htmlspecialchars($e->getMessage().' in '.$e->getFile().':'.$e->getLine())
                    : 'Server error. Check storage/logs/crash.log or set APP_DEBUG=true in .env.';
                return response(
                    '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Error</title></head><body style="font-family:sans-serif;max-width:700px;margin:2rem auto;padding:1rem;"><h1>Application Error</h1><pre style="white-space:pre-wrap;background:#f5f5f5;padding:1rem;overflow:auto;">'.($showError ? $msg."\n\n".htmlspecialchars($e->getTraceAsString()) : $msg).'</pre></body></html>',
                    500,
                    ['Content-Type' => 'text/html; charset=utf-8']
                );
            }
        });
    })->create();
