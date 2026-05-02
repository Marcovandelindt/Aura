<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scambaiter_conversations', function (Blueprint $table) {
            $table->longText('backstory')->nullable()->after('writing_style');
        });
    }

    public function down(): void
    {
        Schema::table('scambaiter_conversations', function (Blueprint $table) {
            $table->dropColumn('backstory');
        });
    }
};
