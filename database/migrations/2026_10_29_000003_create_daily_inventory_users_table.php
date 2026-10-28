<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Who counted a day's inventory (one or more people).
     */
    public function up(): void
    {
        Schema::create('daily_inventory_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('daily_inventory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['daily_inventory_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_inventory_users');
    }
};
