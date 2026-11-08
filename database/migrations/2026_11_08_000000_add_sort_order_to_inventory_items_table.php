<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where the item is listed on each day's inventory sheet (lowest first;
     * ties fall back to creation order). Existing items keep their current
     * position by starting at their id.
     */
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('low_stock_threshold');
        });

        DB::table('inventory_items')->update(['sort_order' => DB::raw('id')]);
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
