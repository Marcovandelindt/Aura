<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->date('date');
            $table->tinyInteger('rating')->unsigned()->nullable();
            $table->foreignId('mood_id')->nullable()->constrained('moods')->nullOnDelete();
            $table->string('location')->nullable();
            $table->unsignedInteger('word_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
