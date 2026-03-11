<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WordTile extends Model
{
    protected $fillable = ['word'];

    public function playerTiles(): HasMany
    {
        return $this->hasMany(GamePlayerTile::class, 'word_tile_id');
    }
}
