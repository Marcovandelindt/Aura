<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('played_tracks', function (Blueprint $table) {
            $table->id();
            $table->string('spotify_track_id', 50);
            $table->timestamp('played_at');
            $table->string('track_name');
            $table->json('artist_names');
            $table->string('album_name');
            $table->string('album_image_url', 500)->nullable();
            $table->integer('duration_ms');
            $table->integer('popularity')->nullable();
            $table->string('preview_url', 500)->nullable();
            $table->string('spotify_uri')->nullable();
            $table->json('contexts')->nullable(); // Store context info (playlist, album, etc.)
            $table->timestamps();
            
            // Indexes
            $table->index('spotify_track_id');
            $table->index('played_at');
            $table->index(['spotify_track_id', 'played_at']);
            
            // Unique constraint to prevent exact duplicates
            $table->unique(['spotify_track_id', 'played_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('played_tracks');
    }
};
