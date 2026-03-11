<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    protected $fillable = ['game_round_id', 'voter_user_id', 'round_submission_id', 'rank'];

    public function gameRound(): BelongsTo
    {
        return $this->belongsTo(GameRound::class, 'game_round_id');
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voter_user_id');
    }

    public function roundSubmission(): BelongsTo
    {
        return $this->belongsTo(RoundSubmission::class, 'round_submission_id');
    }
}
