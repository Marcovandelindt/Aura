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
        Schema::create('play_station_games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('platform'); // PS3, PS4, PS5, PSVITA
            $table->string('image_url')->nullable();
            $table->decimal('hours', 8, 1)->default(0);
            $table->unsignedInteger('sessions')->default(0);
            $table->unsignedInteger('avg_session_minutes')->nullable();
            $table->date('last_played_at')->nullable();
            $table->unsignedInteger('trophies')->nullable();
            $table->decimal('completion_percentage', 5, 2)->nullable();
            $table->string('psn_url')->nullable();
            $table->timestamps();

            $table->unique(['name', 'platform']);
            $table->index('last_played_at');
            $table->index('hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('play_station_games');
    }
};
