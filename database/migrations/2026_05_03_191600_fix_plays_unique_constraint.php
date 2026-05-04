<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plays', function (Blueprint $table) {
            $table->dropUnique(['track_id', 'played_at', 'source']);
            $table->index('track_id', 'plays_track_id_index');
            $table->unique(['played_at', 'source']);
        });
    }

    public function down(): void
    {
        Schema::table('plays', function (Blueprint $table) {
            $table->dropUnique(['played_at', 'source']);
            $table->dropIndex('plays_track_id_index');
            $table->unique(['track_id', 'played_at', 'source']);
        });
    }
};
