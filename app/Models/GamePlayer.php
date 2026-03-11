<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GamePlayer extends Model
{
    protected $table = 'game_player';

    protected $fillable = ['game_id', 'user_id', 'score'];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function playerTiles(): HasMany
    {
        return $this->hasMany(GamePlayerTile::class, 'game_id', 'game_id')
            ->where('game_player_tiles.user_id', $this->user_id);
    }
}
