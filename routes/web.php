<?php

use Illuminate\Support\Facades\Route;

// SPA fallback: serve app for any path that does not start with v1 or api (so API routes are hit first)
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '(?!v1|api).*');
