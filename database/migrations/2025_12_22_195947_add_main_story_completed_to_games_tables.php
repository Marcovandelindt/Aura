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
        Schema::table('nintendo_switch_games', function (Blueprint $table) {
            $table->boolean('main_story_completed')->default(false)->after('play_mode');
        });

        Schema::table('play_station_games', function (Blueprint $table) {
            $table->boolean('main_story_completed')->default(false)->after('play_mode');
        });

        Schema::table('steam_games', function (Blueprint $table) {
            $table->boolean('main_story_completed')->default(false)->after('play_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nintendo_switch_games', function (Blueprint $table) {
            $table->dropColumn('main_story_completed');
        });

        Schema::table('play_station_games', function (Blueprint $table) {
            $table->dropColumn('main_story_completed');
        });

        Schema::table('steam_games', function (Blueprint $table) {
            $table->dropColumn('main_story_completed');
        });
    }
};
