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
        Schema::table('tv_episodes', function (Blueprint $table) {
            $table->dropIndex(['watched']);
            $table->dropColumn('watched');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tv_episodes', function (Blueprint $table) {
            $table->boolean('watched')->default(false);
            $table->index('watched');
        });
    }
};
