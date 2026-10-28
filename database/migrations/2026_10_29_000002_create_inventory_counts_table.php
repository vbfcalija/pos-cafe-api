<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One line on a day's sheet. The item's name and unit are copied on, so
     * renaming an item later never changes past counts. inventory_item_id is
     * empty for "Other" lines, whose name and unit are typed in.
     */
    public function up(): void
    {
        Schema::create('inventory_counts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('daily_inventory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('item_name');
            $table->string('unit', 50);
            $table->decimal('begin', 12, 2)->nullable();
            $table->decimal('ending', 12, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            // Each item once per sheet. "Other" lines (no item) aren't limited.
            $table->unique(['daily_inventory_id', 'inventory_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_counts');
    }
};
