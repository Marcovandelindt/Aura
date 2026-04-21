<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->float('weather_temp')->nullable()->after('map_polyline');
            $table->float('weather_feels_like')->nullable()->after('weather_temp');
            $table->float('weather_precipitation')->nullable()->after('weather_feels_like');
            $table->float('weather_wind_speed')->nullable()->after('weather_precipitation');
            $table->string('weather_condition')->nullable()->after('weather_wind_speed');
            $table->unsignedSmallInteger('weather_condition_code')->nullable()->after('weather_condition');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn([
                'weather_temp',
                'weather_feels_like',
                'weather_precipitation',
                'weather_wind_speed',
                'weather_condition',
                'weather_condition_code',
            ]);
        });
    }
};
