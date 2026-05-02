<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // played_track_mood must be dropped before played_tracks (FK on mood_id, game_id)
        Schema::dropIfExists('played_track_mood');
        Schema::dropIfExists('played_tracks');
        Schema::dropIfExists('lastfm_scrobbles');
    }

    public function down(): void
    {
        // Intentionally not reversible — data was migrated to the new normalized schema.
    }
};
