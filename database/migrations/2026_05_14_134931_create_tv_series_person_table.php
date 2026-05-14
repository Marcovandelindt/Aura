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
        Schema::create('tv_series_person', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tv_series_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->constrained()->cascadeOnDelete();
            $table->string('character')->nullable();
            $table->string('department')->nullable();
            $table->string('job')->nullable();
            $table->unsignedSmallInteger('cast_order')->nullable();
            $table->timestamps();

            $table->unique(['tv_series_id', 'person_id', 'job']);
            $table->index(['person_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tv_series_person');
    }
};
