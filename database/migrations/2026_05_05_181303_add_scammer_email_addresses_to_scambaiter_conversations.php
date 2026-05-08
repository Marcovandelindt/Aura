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
        Schema::table('scambaiter_conversations', function (Blueprint $table) {
            $table->json('scammer_email_addresses')->nullable()->after('scammer_name');
        });
    }

    public function down(): void
    {
        Schema::table('scambaiter_conversations', function (Blueprint $table) {
            $table->dropColumn('scammer_email_addresses');
        });
    }
};
