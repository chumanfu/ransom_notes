<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('voter_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('round_submission_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rank'); // 1 = best, N = worst
            $table->timestamps();
            $table->unique(['game_round_id', 'voter_user_id', 'round_submission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
