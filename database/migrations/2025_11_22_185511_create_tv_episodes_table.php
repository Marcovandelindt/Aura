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
        Schema::create('tv_episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tv_season_id')->constrained('tv_seasons')->cascadeOnDelete();
            $table->integer('tmdb_id')->unique();
            $table->string('name');
            $table->text('overview')->nullable();
            $table->string('still_path')->nullable();
            $table->integer('episode_number');
            $table->date('air_date')->nullable();
            $table->integer('runtime')->nullable();
            $table->decimal('vote_average', 3, 1)->nullable();
            $table->integer('vote_count')->nullable();
            $table->boolean('watched')->default(false);
            $table->timestamps();

            $table->index('tv_season_id');
            $table->index('tmdb_id');
            $table->index('watched');
            $table->unique(['tv_season_id', 'episode_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tv_episodes');
    }
};
