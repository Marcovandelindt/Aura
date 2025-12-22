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
        Schema::table('steam_games', function (Blueprint $table) {
            $table->unsignedTinyInteger('user_rating')->nullable()->after('backlog_status');
            $table->unsignedTinyInteger('critic_rating')->nullable()->after('user_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('steam_games', function (Blueprint $table) {
            $table->dropColumn(['user_rating', 'critic_rating']);
        });
    }
};
