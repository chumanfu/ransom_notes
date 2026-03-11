<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Single-path API gateway: host may return 500 for /api/* and /v1/* before PHP runs.
// All API calls go to /g with X-API-Path header so only one URL is used.
Route::match(['get', 'post', 'put', 'patch', 'delete', 'options'], '/g', function (Request $request) {
    $path = $request->header('X-API-Path');
    if (!$path || !str_starts_with($path, '/')) {
        return response()->json(['error' => 'Missing or invalid X-API-Path header'], 400);
    }
    $sub = Request::create(
        $path,
        $request->method(),
        $request->method() === 'GET' ? $request->query() : [],
        $request->cookies->all(),
        $request->file(),
        $request->server()
    );
    if ($request->getContent()) {
        $sub->setContent($request->getContent());
    }
    $sub->headers->replace($request->headers->all());
    return app()->handle($sub);
});

Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
