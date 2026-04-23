<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entry_tag', function (Blueprint $table) {
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('journal_tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['journal_entry_id', 'journal_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_tag');
    }
};
