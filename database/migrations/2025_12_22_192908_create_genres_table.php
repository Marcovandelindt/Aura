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
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('genre_nintendo_switch_game', function (Blueprint $table) {
            $table->id();
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nintendo_switch_game_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['genre_id', 'nintendo_switch_game_id'], 'genre_switch_unique');
        });

        Schema::create('genre_play_station_game', function (Blueprint $table) {
            $table->id();
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->foreignId('play_station_game_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['genre_id', 'play_station_game_id'], 'genre_ps_unique');
        });

        Schema::create('genre_steam_game', function (Blueprint $table) {
            $table->id();
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->foreignId('steam_game_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['genre_id', 'steam_game_id'], 'genre_steam_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('genre_steam_game');
        Schema::dropIfExists('genre_play_station_game');
        Schema::dropIfExists('genre_nintendo_switch_game');
        Schema::dropIfExists('genres');
    }
};
