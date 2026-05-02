<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scambaiter_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scambaiter_conversation_id')->constrained()->cascadeOnDelete();
            $table->string('sender');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scambaiter_emails');
    }
};
