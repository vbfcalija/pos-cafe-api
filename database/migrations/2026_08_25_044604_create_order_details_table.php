<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            // price/cost/tax_percentage are snapshotted from the variant/product/tax_rate
            // at order time — never re-derived from the current catalog. See SKILL.md.
            $table->decimal('price', 18, 2)->unsigned();
            $table->decimal('cost', 18, 2)->unsigned();
            $table->decimal('tax_percentage', 5, 2);
            $table->foreignId('discount_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
