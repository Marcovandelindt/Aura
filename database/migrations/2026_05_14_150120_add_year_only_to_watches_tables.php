<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('episode_watches', function (Blueprint $table) {
            $table->boolean('year_only')->default(false)->after('watched_at');
        });

        Schema::table('movie_watches', function (Blueprint $table) {
            $table->boolean('year_only')->default(false)->after('watched_at');
        });
    }

    public function down(): void
    {
        Schema::table('episode_watches', function (Blueprint $table) {
            $table->dropColumn('year_only');
        });

        Schema::table('movie_watches', function (Blueprint $table) {
            $table->dropColumn('year_only');
        });
    }
};
