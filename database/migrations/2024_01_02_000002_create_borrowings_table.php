<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->date('borrow_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'active', 'overdue', 'returned'])->default('pending');
            $table->timestamp('approved_date')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('handover_by')->nullable();
            $table->timestamp('handover_date')->nullable();
            $table->text('handover_notes')->nullable();
            $table->string('return_pic')->nullable();
            $table->text('return_notes')->nullable();
            $table->text('purpose')->nullable();
            $table->timestamps();

            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrowings');
    }
};
