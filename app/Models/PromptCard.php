<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromptCard extends Model
{
    protected $fillable = ['text', 'created_by'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class, 'prompt_card_id');
    }
}
