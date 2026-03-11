<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Game::query()->with('creator:id,name', 'players:id,name');
        if (! $request->user()->isAdmin()) {
            $query->whereHas('players', fn ($q) => $q->where('user_id', $request->user()->id));
        }
        $games = $query->latest()->paginate(20);
        return response()->json($games);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $game = Game::create([
            'name' => $request->input('name', 'Ransom Notes'),
            'code' => Game::generateCode(),
            'created_by' => $request->user()->id,
            'status' => 'lobby',
        ]);

        $game->players()->attach($request->user()->id, ['score' => 0]);

        return response()->json($game->load('creator:id,name', 'players:id,name'), 201);
    }

    public function show(Game $game): JsonResponse
    {
        $this->authorizeGameAccess($game);
        $game->load('creator:id,name', 'players:id,name', 'rounds.promptCard');
        return response()->json($game);
    }

    public function update(Request $request, Game $game): JsonResponse
    {
        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
        ]);
        $game->update($request->only(['name']));
        return response()->json($game->load('creator:id,name', 'players:id,name'));
    }

    public function destroy(Game $game): JsonResponse
    {
        $game->delete();
        return response()->json(null, 204);
    }

    public function join(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $game = Game::where('code', strtoupper($request->code))->first();

        if (! $game) {
            return response()->json(['message' => 'Game not found.'], 404);
        }

        if ($game->status !== 'lobby') {
            return response()->json(['message' => 'Game has already started. No one can join.'], 422);
        }

        if ($game->players()->where('user_id', $request->user()->id)->exists()) {
            return response()->json($game->load('creator:id,name', 'players:id,name'));
        }

        $game->players()->attach($request->user()->id, ['score' => 0]);

        return response()->json($game->load('creator:id,name', 'players:id,name'));
    }

    public function state(Game $game): JsonResponse
    {
        $this->authorizeGameAccess($game);
        $round = $game->currentRound() ?? $game->rounds()->latest('id')->first();
        $state = [
            'game' => $game->only(['id', 'name', 'code', 'status', 'started_at']),
            'players' => $game->players->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'score' => $p->pivot->score,
            ]),
            'current_round' => null,
        ];

        if ($round) {
            $tileIds = $round->submissions->pluck('tile_order')->flatten()->unique()->filter()->values();
            $tilesMap = \App\Models\GamePlayerTile::with('wordTile')
                ->whereIn('id', $tileIds)
                ->get()
                ->keyBy('id');
            $state['current_round'] = [
                'id' => $round->id,
                'status' => $round->status,
                'stopped_at' => $round->stopped_at?->toIso8601String(),
                'prompt_card' => $round->promptCard->only(['id', 'text']),
                'submissions' => $round->submissions->map(fn ($s) => [
                    'id' => $s->id,
                    'user_id' => $s->user_id,
                    'user_name' => $s->user->name,
                    'tile_order' => $s->tile_order,
                    'words' => array_map(fn ($id) => $tilesMap->get($id)?->wordTile?->word ?? '', $s->tile_order ?? []),
                    'total_rank' => $round->status === 'completed' ? $s->totalRank() : null,
                ]),
            ];
        }

        return response()->json($state);
    }

    private function authorizeGameAccess(Game $game): void
    {
        $user = request()->user();
        if (! $user->isAdmin() && ! $game->players()->where('user_id', $user->id)->exists()) {
            abort(403, 'You are not in this game.');
        }
    }
}
