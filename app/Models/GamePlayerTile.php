<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamePlayerTile extends Model
{
    protected $fillable = ['game_id', 'user_id', 'word_tile_id'];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wordTile(): BelongsTo
    {
        return $this->belongsTo(WordTile::class, 'word_tile_id');
    }
}
