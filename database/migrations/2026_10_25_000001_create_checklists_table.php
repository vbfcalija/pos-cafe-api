<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One checklist per branch per business day. It holds both halves of the
     * day — the opening and the closing tasks — the same way the printed
     * barista sheet does.
     */
    public function up(): void
    {
        Schema::create('checklists', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->date('date');
            $table->dateTime('time_started')->nullable();
            $table->dateTime('time_finished')->nullable();
            $table->enum('status', ['pending', 'verified'])->default('pending'); // ChecklistStatus enum
            $table->timestamps();

            $table->unique(['branch_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklists');
    }
};
