<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_code', 30)->unique();
            $table->string('group_name');
            $table->timestamps();
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_group_id')->nullable()->after('asset_id');
            $table->foreign('asset_group_id')->references('id')->on('asset_groups')->onDelete('set null');
        });

        Schema::table('borrowings', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_group_id')->nullable()->after('asset_id');
            $table->unsignedBigInteger('parent_borrowing_id')->nullable()->after('purpose');
            $table->foreign('asset_group_id')->references('id')->on('asset_groups')->onDelete('set null');
            $table->foreign('parent_borrowing_id')->references('id')->on('borrowings')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropForeign(['asset_group_id']);
            $table->dropForeign(['parent_borrowing_id']);
            $table->dropColumn(['asset_group_id', 'parent_borrowing_id']);
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['asset_group_id']);
            $table->dropColumn('asset_group_id');
        });

        Schema::dropIfExists('asset_groups');
    }
};
