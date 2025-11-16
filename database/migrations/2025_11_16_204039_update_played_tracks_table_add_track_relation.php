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
        Schema::table('played_tracks', function (Blueprint $table) {
            // Add foreign key to tracks table
            $table->foreignId('track_id')->after('id')->constrained('tracks')->cascadeOnDelete();
            
            // Remove redundant columns that are now in tracks table
            $table->dropColumn([
                'track_name',
                'artist_names',
                'album_name',
                'album_image_url',
                'duration_ms',
                'popularity',
                'preview_url',
                'spotify_uri'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('played_tracks', function (Blueprint $table) {
            // Add back the columns
            $table->string('track_name')->after('played_at');
            $table->json('artist_names')->after('track_name');
            $table->string('album_name')->after('artist_names');
            $table->string('album_image_url', 500)->nullable()->after('album_name');
            $table->integer('duration_ms')->after('album_image_url');
            $table->integer('popularity')->nullable()->after('popularity');
            $table->string('preview_url', 500)->nullable()->after('preview_url');
            $table->string('spotify_uri')->nullable()->after('spotify_uri');
            
            // Remove foreign key
            $table->dropForeign(['track_id']);
            $table->dropColumn('track_id');
        });
    }
};
