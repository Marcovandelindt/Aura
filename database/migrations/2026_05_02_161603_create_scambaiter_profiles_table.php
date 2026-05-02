<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scambaiter_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->default('');
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('nationality')->nullable();
            $table->string('location')->nullable();
            $table->string('occupation')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('additional_facts')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scambaiter_profiles');
    }
};
