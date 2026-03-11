<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Game extends Model
{
    protected $fillable = ['name', 'code', 'created_by', 'status', 'started_at'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'game_player')
            ->withPivot('score')
            ->withTimestamps();
    }

    public function gamePlayers(): HasMany
    {
        return $this->hasMany(GamePlayer::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class)->orderBy('id');
    }

    public function currentRound(): ?GameRound
    {
        return $this->rounds()->whereIn('status', ['building', 'voting'])->latest('id')->first();
    }

    public function playerTilesForUser(int $userId): Collection
    {
        return GamePlayerTile::where('game_id', $this->id)
            ->where('user_id', $userId)
            ->with('wordTile')
            ->get();
    }

    public static function generateCode(): string
    {
        do {
            $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        } while (static::where('code', $code)->exists());
        return $code;
    }
}
