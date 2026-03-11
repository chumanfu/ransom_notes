<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameRound;
use App\Models\RoundSubmission;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function index(Game $game, GameRound $round): JsonResponse
    {
        if ($round->game_id !== $game->id) {
            abort(404);
        }
        $this->ensureInGame($game);

        $votes = Vote::where('game_round_id', $round->id)
            ->where('voter_user_id', request()->user()->id)
            ->get()
            ->keyBy('round_submission_id');

        return response()->json(['votes' => $votes->map(fn ($v) => ['round_submission_id' => $v->round_submission_id, 'rank' => $v->rank])->values()]);
    }

    public function store(Request $request, Game $game, GameRound $round): JsonResponse
    {
        if ($round->game_id !== $game->id) {
            abort(404);
        }
        if ($round->status !== 'voting') {
            return response()->json(['message' => 'This round is not in voting phase.'], 422);
        }

        $this->ensureInGame($game);
        $user = $request->user();
        $submissionIds = $round->submissions()->pluck('id')->toArray();

        $request->validate([
            'votes' => ['required', 'array'],
            'votes.*.round_submission_id' => ['required', 'integer', 'in:'.implode(',', $submissionIds)],
            'votes.*.rank' => ['required', 'integer', 'min:1', 'max:'.max(1, count($submissionIds))],
        ]);

        $votes = $request->votes;
        $uniqueRanks = collect($votes)->pluck('rank')->unique();
        if ($uniqueRanks->count() !== count($submissionIds) || $uniqueRanks->count() !== count($votes)) {
            return response()->json(['message' => 'Each submission must receive exactly one rank from 1 (best) to N (worst).'], 422);
        }

        foreach ($votes as $v) {
            Vote::updateOrCreate(
                [
                    'game_round_id' => $round->id,
                    'voter_user_id' => $user->id,
                    'round_submission_id' => $v['round_submission_id'],
                ],
                ['rank' => (int) $v['rank']]
            );
        }

        return response()->json(['message' => 'Votes saved.']);
    }

    private function ensureInGame(Game $game): void
    {
        if (! $game->players()->where('user_id', request()->user()->id)->exists()) {
            abort(403, 'You are not in this game.');
        }
    }
}