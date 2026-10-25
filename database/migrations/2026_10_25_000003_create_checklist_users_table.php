<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The baristas on a day's checklist. Several people can be openers and
     * several can be closers; the same person can be both.
     */
    public function up(): void
    {
        Schema::create('checklist_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('checklist_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['opener', 'closer']); // ChecklistType enum
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['checklist_id', 'type', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_users');
    }
};
