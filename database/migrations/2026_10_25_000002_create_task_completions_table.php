<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per task on a given day's checklist. completed_at is the
     * "timestamp" on the ERD: when the task was ticked off (null while it
     * isn't done).
     */
    public function up(): void
    {
        Schema::create('task_completions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('checklist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->restrictOnDelete();
            $table->boolean('is_done')->default(false);
            $table->text('remarks')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['checklist_id', 'task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_completions');
    }
};
