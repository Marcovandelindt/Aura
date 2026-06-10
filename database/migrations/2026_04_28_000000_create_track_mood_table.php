<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('track_mood', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained('tracks')->cascadeOnDelete();
            $table->foreignId('mood_id')->constrained('moods')->cascadeOnDelete();
            $table->foreignId('game_id')->nullable()->constrained('games')->nullOnDelete();
            $table->timestamps();

            $table->unique(['track_id', 'mood_id']);
            $table->index('game_id');
        });

        if (DB::getDriverName() === 'mysql') {
            // Migrate data from played_track_mood → track_mood via tracks.spotify_track_id
            DB::statement('
                INSERT INTO track_mood (track_id, mood_id, game_id, created_at, updated_at)
                SELECT t.id, ptm.mood_id, ptm.game_id, ptm.created_at, ptm.updated_at
                FROM played_track_mood ptm
                INNER JOIN tracks t ON t.spotify_track_id = ptm.spotify_track_id
                ON DUPLICATE KEY UPDATE game_id = VALUES(game_id), updated_at = VALUES(updated_at)
            ');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('track_mood');
    }
};
