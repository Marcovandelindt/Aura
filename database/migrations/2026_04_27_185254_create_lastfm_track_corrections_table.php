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
        Schema::create('lastfm_track_corrections', function (Blueprint $table) {
            $table->id();
            $table->string('track_name');
            $table->string('artist_name');
            $table->json('all_artist_names');
            $table->timestamps();

            $table->unique(['track_name', 'artist_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lastfm_track_corrections');
    }
};
