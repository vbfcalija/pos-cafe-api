<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional: an item counts as low stock when its latest Ending is at or
     * below this level (in the item's own unit). Empty means "not tracked";
     * an Ending of 0 is still reported as out of stock.
     */
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->decimal('low_stock_threshold', 12, 2)->nullable()->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn('low_stock_threshold');
        });
    }
};
