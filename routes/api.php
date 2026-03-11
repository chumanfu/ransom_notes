<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\PromptCardController;
use App\Http\Controllers\Api\GameRoundController;
use App\Http\Controllers\Api\PlayerTilesController;
use App\Http\Controllers\Api\VoteController;
use Illuminate\Support\Facades\Route;

// CORS preflight: browser sends OPTIONS before POST with JSON; must return 200 so the real request is sent
Route::options('{any}', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept')
        ->header('Access-Control-Max-Age', '86400');
})->where('any', '.*');

// Debug: hit GET or POST /api/ping to confirm the request reaches Laravel (writes to public/crash.log)
Route::match(['get', 'post'], '/ping', function () {
    $crashLog = base_path('public/crash.log');
    @file_put_contents($crashLog, date('c').' /api/ping '.(request()->method())."\n", FILE_APPEND);
    return response()->json(['pong' => true]);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/games', [GameController::class, 'index']);
    Route::post('/games', [GameController::class, 'store'])->middleware('admin');
    Route::get('/games/{game}', [GameController::class, 'show']);
    Route::post('/games/join', [GameController::class, 'join']);
    Route::get('/games/{game}/state', [GameController::class, 'state']);

    Route::get('/prompt-cards', [PromptCardController::class, 'index']);
    Route::post('/prompt-cards', [PromptCardController::class, 'store'])->middleware('admin');
    Route::post('/prompt-cards/import', [PromptCardController::class, 'import'])->middleware('admin');
    Route::delete('/prompt-cards/{promptCard}', [PromptCardController::class, 'destroy'])->middleware('admin');

    Route::post('/games/{game}/rounds/start', [GameRoundController::class, 'start'])->middleware('admin');
    Route::post('/games/{game}/rounds/stop', [GameRoundController::class, 'stop'])->middleware('admin');
    Route::post('/games/{game}/rounds/{round}/submission', [GameRoundController::class, 'submit']);
    Route::post('/games/{game}/rounds/complete', [GameRoundController::class, 'completeRound'])->middleware('admin');

    Route::post('/games/{game}/tiles/draw', [PlayerTilesController::class, 'draw']);
    Route::get('/games/{game}/tiles', [PlayerTilesController::class, 'index']);
    Route::post('/games/{game}/tiles/top-up', [PlayerTilesController::class, 'topUp']);

    Route::post('/games/{game}/rounds/{round}/votes', [VoteController::class, 'store']);
    Route::get('/games/{game}/rounds/{round}/votes', [VoteController::class, 'index']);
});
