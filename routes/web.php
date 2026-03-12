<?php

use Illuminate\Support\Facades\Route;

// Minimal HTML error page so we see the real error on the server (instead of generic 500)
$spaOrError = function () {
    try {
        return view('app');
    } catch (Throwable $e) {
        $logDir = storage_path('logs');
        if (! is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        @file_put_contents(
            $logDir.'/crash.log',
            date('c').' SPA view: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine().PHP_EOL.$e->getTraceAsString().PHP_EOL,
            FILE_APPEND
        );
        $showError = config('app.debug');
        $message = $showError
            ? htmlspecialchars($e->getMessage().' in '.$e->getFile().':'.$e->getLine())
            : 'Server error. Check storage/logs/crash.log or set APP_DEBUG=true in .env to see details.';
        return response(
            '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Error</title></head><body style="font-family:sans-serif;max-width:600px;margin:2rem auto;padding:1rem;"><h1>Application Error</h1><pre style="white-space:pre-wrap;background:#f5f5f5;padding:1rem;">' . $message . '</pre></body></html>',
            500,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }
};

// SPA: serve app shell for these paths so Vue Router can handle them. All other paths (e.g. /api/*) hit API routes.
Route::get('/', $spaOrError);
Route::get('/login', $spaOrError);
Route::get('/register', $spaOrError);
Route::get('/games/{id}', $spaOrError)->where('id', '[0-9]+');
Route::get('/admin/cards', $spaOrError);
