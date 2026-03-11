<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Root handler: only "/" is known to reach Laravel on IONOS. API traffic uses same URL with X-API-Path header.
Route::match(['get', 'post', 'put', 'patch', 'delete', 'options'], '/', function (Request $request) {
    $path = $request->header('X-API-Path');
    if ($path && str_starts_with($path, '/')) {
        $sub = Request::create(
            $path,
            $request->method(),
            $request->method() === 'GET' ? $request->query() : [],
            $request->cookies->all(),
            $request->file(),
            $request->server(),
            $request->getContent()
        );
        $sub->headers->replace($request->headers->all());
        return app()->handle($sub);
    }
    return view('app');
});

Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
