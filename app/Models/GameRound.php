<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameRound extends Model
{
    protected $fillable = ['game_id', 'prompt_card_id', 'status', 'stopped_at'];

    protected function casts(): array
    {
        return [
            'stopped_at' => 'datetime',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function promptCard(): BelongsTo
    {
        return $this->belongsTo(PromptCard::class, 'prompt_card_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(RoundSubmission::class, 'game_round_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class, 'game_round_id');
    }
}
