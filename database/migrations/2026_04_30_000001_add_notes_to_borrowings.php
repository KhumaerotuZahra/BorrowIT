<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE borrowings ADD COLUMN notes TEXT NULL AFTER purpose');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE borrowings DROP COLUMN notes');
    }
};
