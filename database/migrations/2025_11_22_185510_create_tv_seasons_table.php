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
        Schema::create('tv_seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tv_series_id')->constrained('tv_series')->cascadeOnDelete();
            $table->integer('tmdb_id')->unique();
            $table->string('name');
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->integer('season_number');
            $table->date('air_date')->nullable();
            $table->integer('episode_count')->default(0);
            $table->integer('episodes_watched')->default(0);
            $table->timestamps();

            $table->index('tv_series_id');
            $table->index('tmdb_id');
            $table->unique(['tv_series_id', 'season_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tv_seasons');
    }
};
