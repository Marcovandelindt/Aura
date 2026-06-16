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
        Schema::create('agreement_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained()->cascadeOnDelete();
            $table->date('checked_on');
            $table->enum('status', ['respected', 'partially', 'not_respected']);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['agreement_id', 'checked_on']);
            $table->index('checked_on');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agreement_check_ins');
    }
};
