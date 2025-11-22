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
        Schema::create('tv_series', function (Blueprint $table) {
            $table->id();
            $table->integer('tmdb_id')->unique();
            $table->string('name');
            $table->string('original_name')->nullable();
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->date('first_air_date')->nullable();
            $table->date('last_air_date')->nullable();
            $table->decimal('vote_average', 3, 1)->nullable();
            $table->integer('vote_count')->nullable();
            $table->json('genres')->nullable();
            $table->string('status')->nullable();
            $table->string('original_language', 10)->nullable();
            $table->integer('number_of_seasons')->default(0);
            $table->integer('number_of_episodes')->default(0);
            $table->integer('episodes_watched')->default(0);
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->timestamp('last_watched_at')->nullable();
            $table->timestamp('first_watched_at')->nullable();
            $table->timestamps();

            $table->index('tmdb_id');
            $table->index('last_watched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tv_series');
    }
};
