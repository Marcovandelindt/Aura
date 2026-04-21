<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('strava_id')->unique();
            $table->string('name');
            $table->string('type');
            $table->string('sport_type');
            $table->float('distance')->default(0);
            $table->integer('moving_time')->default(0);
            $table->integer('elapsed_time')->default(0);
            $table->float('total_elevation_gain')->default(0);
            $table->dateTime('start_date');
            $table->dateTime('start_date_local');
            $table->string('timezone')->nullable();
            $table->float('average_speed')->nullable();
            $table->float('max_speed')->nullable();
            $table->float('average_heartrate')->nullable();
            $table->integer('max_heartrate')->nullable();
            $table->float('calories')->nullable();
            $table->text('description')->nullable();
            $table->text('map_polyline')->nullable();
            $table->timestamps();

            $table->index('start_date');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
