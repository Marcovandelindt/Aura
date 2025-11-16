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
        Schema::create('played_track_mood', function (Blueprint $table) {
            $table->id();
            $table->string('spotify_track_id', 50); // Reference to track
            $table->foreignId('mood_id')->constrained('moods')->cascadeOnDelete();
            $table->timestamps();
            
            // Prevent duplicate mood assignments to same track
            $table->unique(['spotify_track_id', 'mood_id']);
            $table->index('spotify_track_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('played_track_mood');
    }
};
