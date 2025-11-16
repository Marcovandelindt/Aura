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
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->string('spotify_track_id', 50)->unique();
            $table->string('track_name');
            $table->json('artist_names');
            $table->json('artist_ids'); // Store Spotify artist IDs
            $table->string('album_name');
            $table->string('album_image_url', 500)->nullable();
            $table->integer('duration_ms');
            $table->integer('popularity')->nullable();
            $table->string('preview_url', 500)->nullable();
            $table->string('spotify_uri');
            $table->json('genres')->nullable(); // Store all unique genres from all artists
            $table->timestamps();
            
            // Indexes
            $table->index('spotify_track_id');
            $table->index('track_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
