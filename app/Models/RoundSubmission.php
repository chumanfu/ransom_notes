<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoundSubmission extends Model
{
    protected $fillable = ['game_round_id', 'user_id', 'tile_order'];

    protected function casts(): array
    {
        return [
            'tile_order' => 'array',
        ];
    }

    public function gameRound(): BelongsTo
    {
        return $this->belongsTo(GameRound::class, 'game_round_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class, 'round_submission_id');
    }

    public function totalRank(): int
    {
        return (int) $this->votes()->sum('rank');
    }
}
