<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\PromptCardController;
use App\Http\Controllers\Api\GameRoundController;
use App\Http\Controllers\Api\PlayerTilesController;
use App\Http\Controllers\Api\VoteController;
use Illuminate\Support\Facades\Route;

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
