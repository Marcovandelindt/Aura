<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lastfm_scrobbles', function (Blueprint $table) {
            $table->id();
            $table->string('track_name');
            $table->string('artist_name');
            $table->string('album_name')->nullable();
            $table->string('album_image_url')->nullable();
            $table->timestamp('played_at');

            // Spotify enrichment (filled in later, optional)
            $table->string('spotify_track_id')->nullable()->index();
            $table->integer('duration_ms')->nullable();
            $table->integer('popularity')->nullable();
            $table->string('spotify_uri')->nullable();
            $table->timestamp('enriched_at')->nullable();
            $table->unsignedTinyInteger('enrichment_confidence')->nullable();

            $table->timestamps();

            // Prevent duplicate imports of the same play
            $table->unique(['track_name', 'artist_name', 'played_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lastfm_scrobbles');
    }
};
