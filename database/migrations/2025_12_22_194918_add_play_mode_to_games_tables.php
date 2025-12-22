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
            $table->string('play_mode')->nullable()->after('backlog_status');
        });

        Schema::table('play_station_games', function (Blueprint $table) {
            $table->string('play_mode')->nullable()->after('backlog_status');
        });

        Schema::table('steam_games', function (Blueprint $table) {
            $table->string('play_mode')->nullable()->after('backlog_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nintendo_switch_games', function (Blueprint $table) {
            $table->dropColumn('play_mode');
        });

        Schema::table('play_station_games', function (Blueprint $table) {
            $table->dropColumn('play_mode');
        });

        Schema::table('steam_games', function (Blueprint $table) {
            $table->dropColumn('play_mode');
        });
    }
};
