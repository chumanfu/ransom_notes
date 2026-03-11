<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('round_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('tile_order'); // array of game_player_tile ids in display order (max 30)
            $table->timestamps();
            $table->unique(['game_round_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('round_submissions');
    }
};
