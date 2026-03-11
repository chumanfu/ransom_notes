<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GamePlayerTile;
use App\Models\WordTile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerTilesController extends Controller
{
    private const MAX_TILES = 60;
    private const DRAW_COUNT = 15;

    public function index(Game $game): JsonResponse
    {
        $this->ensureInGame($game);
        $tiles = $game->playerTilesForUser(request()->user()->id);
        return response()->json([
            'tiles' => $tiles->map(fn ($t) => [
                'id' => $t->id,
                'word' => $t->wordTile->word,
                'word_tile_id' => $t->word_tile_id,
            ]),
            'count' => $tiles->count(),
            'max' => self::MAX_TILES,
        ]);
    }

    public function draw(Request $request, Game $game): JsonResponse
    {
        $this->ensureInGame($game);
        $user = $request->user();
        $current = GamePlayerTile::where('game_id', $game->id)->where('user_id', $user->id)->count();
        if ($current >= self::MAX_TILES) {
            return response()->json(['message' => 'You already have the maximum of '.self::MAX_TILES.' tiles.'], 422);
        }

        $toDraw = min(self::DRAW_COUNT, self::MAX_TILES - $current);
        $wordTiles = WordTile::inRandomOrder()->limit($toDraw * 2)->get();
        if ($wordTiles->isEmpty()) {
            return response()->json(['message' => 'No word tiles in the pool. Admin must add words.'], 422);
        }

        $drawn = $wordTiles->take($toDraw);
        $created = [];
        foreach ($drawn as $wt) {
            $c = GamePlayerTile::create([
                'game_id' => $game->id,
                'user_id' => $user->id,
                'word_tile_id' => $wt->id,
            ]);
            $c->load('wordTile');
            $created[] = ['id' => $c->id, 'word' => $c->wordTile->word, 'word_tile_id' => $c->word_tile_id];
        }

        $newCount = $current + count($created);
        return response()->json([
            'drawn' => $created,
            'count' => $newCount,
            'max' => self::MAX_TILES,
        ]);
    }

    public function topUp(Request $request, Game $game): JsonResponse
    {
        $this->ensureInGame($game);
        $round = $game->currentRound();
        if ($round && $round->status !== 'completed') {
            return response()->json(['message' => 'Round must be completed before topping up. Wait for admin to complete the round.'], 422);
        }

        return $this->draw($request, $game);
    }

    private function ensureInGame(Game $game): void
    {
        if (! $game->players()->where('user_id', request()->user()->id)->exists()) {
            abort(403, 'You are not in this game.');
        }
    }
}
