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
        Schema::create('nintendo_switch_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nintendo_switch_game_id')->constrained()->cascadeOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->unsignedInteger('duration_minutes');
            $table->timestamps();

            $table->unique(['nintendo_switch_game_id', 'started_at'], 'ns_sessions_game_started_unique');
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nintendo_switch_sessions');
    }
};
