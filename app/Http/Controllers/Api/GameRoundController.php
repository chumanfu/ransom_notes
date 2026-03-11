<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameRound;
use App\Models\PromptCard;
use App\Models\RoundSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameRoundController extends Controller
{
    public function start(Request $request, Game $game): JsonResponse
    {
        if ($game->status !== 'lobby') {
            return response()->json(['message' => 'Game already started.'], 422);
        }

        $card = PromptCard::inRandomOrder()->first();
        if (! $card) {
            return response()->json(['message' => 'No prompt cards available. Add or import cards first.'], 422);
        }

        DB::transaction(function () use ($game, $card) {
            $game->update(['status' => 'in_progress', 'started_at' => now()]);
            GameRound::create([
                'game_id' => $game->id,
                'prompt_card_id' => $card->id,
                'status' => 'building',
            ]);
        });

        return response()->json($game->load('currentRound.promptCard'));
    }

    public function stop(Request $request, Game $game): JsonResponse
    {
        $round = $game->currentRound();
        if (! $round || $round->status !== 'building') {
            return response()->json(['message' => 'No round in building phase to stop.'], 422);
        }

        $round->update(['status' => 'voting', 'stopped_at' => now()]);
        return response()->json($game->fresh()->load('currentRound.promptCard', 'currentRound.submissions.user'));
    }

    public function submit(Request $request, Game $game, GameRound $round): JsonResponse
    {
        if ($round->game_id !== $game->id) {
            abort(404);
        }
        if ($round->status !== 'building') {
            return response()->json(['message' => 'Round is not accepting submissions.'], 422);
        }

        $playerTileIds = $game->playerTilesForUser($request->user()->id)->pluck('id')->toArray();
        $request->validate([
            'tile_order' => ['required', 'array', 'max:30'],
            'tile_order.*' => ['integer', 'in:'.implode(',', $playerTileIds)],
        ]);

        $tileOrder = array_values(array_map('intval', $request->tile_order));

        RoundSubmission::updateOrCreate(
            ['game_round_id' => $round->id, 'user_id' => $request->user()->id],
            ['tile_order' => $tileOrder]
        );

        return response()->json(['message' => 'Submission saved.']);
    }

    public function completeRound(Request $request, Game $game): JsonResponse
    {
        $round = $game->currentRound();
        if (! $round || $round->status !== 'voting') {
            return response()->json(['message' => 'No round in voting phase to complete.'], 422);
        }

        $submissions = $round->submissions()->with('votes')->get();
        $best = $submissions->sortBy(fn ($s) => $s->totalRank())->first();
        if ($best) {
            $pivot = $game->gamePlayers()->where('user_id', $best->user_id)->first();
            if ($pivot) {
                $pivot->increment('score');
            }
        }

        $round->update(['status' => 'completed']);
        $game->load('players');

        $winner = $game->players->first(fn ($p) => $p->pivot->score >= 5);
        if ($winner) {
            $game->update(['status' => 'completed']);
        }

        return response()->json([
            'round_completed' => true,
            'round_winner_user_id' => $best?->user_id,
            'game' => $game->fresh()->load('players'),
            'game_ended' => (bool) $winner,
        ]);
    }
}
