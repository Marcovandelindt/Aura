<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tv_series_person', function (Blueprint $table) {
            $table->text('character')->nullable()->change();
        });

        Schema::table('movie_person', function (Blueprint $table) {
            $table->text('character')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tv_series_person', function (Blueprint $table) {
            $table->string('character')->nullable()->change();
        });

        Schema::table('movie_person', function (Blueprint $table) {
            $table->string('character')->nullable()->change();
        });
    }
};
