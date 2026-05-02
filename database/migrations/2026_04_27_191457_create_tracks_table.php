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
        Schema::dropIfExists('tracks');

        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('spotify_track_id', 50)->nullable()->unique();
            $table->foreignId('album_id')->nullable()->constrained('albums')->nullOnDelete();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedTinyInteger('popularity')->nullable();
            $table->string('preview_url')->nullable();
            $table->string('spotify_uri')->nullable();
            $table->timestamps();

            $table->index('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
