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
        if (Schema::hasColumn('played_tracks', 'track_id')) {
            Schema::table('played_tracks', function (Blueprint $table) {
                $table->dropColumn('track_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('played_tracks', function (Blueprint $table) {
            // Add back the track_id foreign key
            $table->foreignId('track_id')->after('id')->constrained('tracks')->cascadeOnDelete();
        });
    }
};
