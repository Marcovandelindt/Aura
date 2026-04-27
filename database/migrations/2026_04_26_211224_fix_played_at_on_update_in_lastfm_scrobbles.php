<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL implicitly adds ON UPDATE CURRENT_TIMESTAMP to the first TIMESTAMP NOT NULL
        // column when explicit_defaults_for_timestamp is OFF. This causes every UPDATE on a
        // lastfm_scrobbles row to overwrite played_at with now(), breaking the unique constraint
        // (track_name, artist_name, played_at) when multiple rows for the same track are updated.
        DB::statement('ALTER TABLE lastfm_scrobbles MODIFY COLUMN `played_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE lastfm_scrobbles MODIFY COLUMN `played_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }
};
