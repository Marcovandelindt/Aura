<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scambaiter_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('scammer_name')->nullable();
            $table->string('intelligence_level')->default('average');
            $table->json('personality_traits')->nullable();
            $table->text('personal_details')->nullable();
            $table->text('writing_style')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scambaiter_conversations');
    }
};
