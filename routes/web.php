<?php

use Illuminate\Support\Facades\Route;

// SPA: serve app shell for these paths so Vue Router can handle them. All other paths (e.g. /api/*) hit API routes.
Route::get('/', fn () => view('app'));
Route::get('/login', fn () => view('app'));
Route::get('/register', fn () => view('app'));
Route::get('/games/{id}', fn () => view('app'))->where('id', '[0-9]+');
Route::get('/admin/cards', fn () => view('app'));
