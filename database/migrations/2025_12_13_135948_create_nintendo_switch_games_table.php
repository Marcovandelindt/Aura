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
        Schema::create('nintendo_switch_games', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('image_url')->nullable();
            $table->decimal('hours', 8, 1)->default(0);
            $table->unsignedInteger('sessions')->default(0);
            $table->unsignedInteger('avg_session_minutes')->nullable();
            $table->date('last_played_at')->nullable();
            $table->timestamps();

            $table->index('last_played_at');
            $table->index('hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nintendo_switch_games');
    }
};
