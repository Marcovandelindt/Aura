<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nintendo_switch_sessions', function (Blueprint $table) {
            $table->dropForeign(['nintendo_switch_game_id']);
            $table->dropIndex('ns_sessions_game_started_unique');
            $table->dropIndex('nintendo_switch_sessions_started_at_index');
            $table->dropColumn('ended_at');
        });

        Schema::table('nintendo_switch_sessions', function (Blueprint $table) {
            $table->date('started_at')->change();
            $table->foreign('nintendo_switch_game_id')->references('id')->on('nintendo_switch_games')->cascadeOnDelete();
            $table->unique(['nintendo_switch_game_id', 'started_at'], 'ns_sessions_game_date_unique');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('nintendo_switch_sessions', function (Blueprint $table) {
            $table->dropForeign(['nintendo_switch_game_id']);
            $table->dropIndex('ns_sessions_game_date_unique');
            $table->dropIndex('nintendo_switch_sessions_started_at_index');
        });

        Schema::table('nintendo_switch_sessions', function (Blueprint $table) {
            $table->dateTime('started_at')->change();
            $table->dateTime('ended_at')->nullable()->after('started_at');
            $table->foreign('nintendo_switch_game_id')->references('id')->on('nintendo_switch_games')->cascadeOnDelete();
            $table->unique(['nintendo_switch_game_id', 'started_at'], 'ns_sessions_game_started_unique');
            $table->index('started_at');
        });
    }
};
