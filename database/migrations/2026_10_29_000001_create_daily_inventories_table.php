<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One inventory sheet per branch per business day.
     */
    public function up(): void
    {
        Schema::create('daily_inventories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->date('date');
            $table->enum('status', ['pending', 'verified'])->default('pending'); // DailyInventoryStatus enum
            $table->timestamps();

            $table->unique(['branch_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_inventories');
    }
};
