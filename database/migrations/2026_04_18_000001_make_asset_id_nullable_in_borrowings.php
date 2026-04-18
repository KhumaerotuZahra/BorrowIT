<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the foreign key constraint first
        DB::statement('ALTER TABLE borrowings DROP FOREIGN KEY borrowings_asset_id_foreign');
        
        // Make asset_id nullable
        DB::statement('ALTER TABLE borrowings MODIFY asset_id BIGINT UNSIGNED NULL');
        
        // Re-add foreign key
        DB::statement('ALTER TABLE borrowings ADD CONSTRAINT borrowings_asset_id_foreign FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE borrowings DROP FOREIGN KEY borrowings_asset_id_foreign');
        DB::statement('ALTER TABLE borrowings MODIFY asset_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE borrowings ADD CONSTRAINT borrowings_asset_id_foreign FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE');
    }
};
