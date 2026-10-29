<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * One-off backfill: copies each product variant's current cost onto every
 * order line sold for it, so profit reports use the latest cost.
 *
 * Order lines normally keep the cost from the moment of sale. Run this only
 * when those snapshots are wrong (e.g. sales made before costs were set):
 *
 *     php artisan db:seed --class=OrderDetailCostSeeder
 *
 * Deliberately not part of DatabaseSeeder, so a regular seed never rewrites
 * past sales. Uses a single query-builder update: no model events, so no
 * activity-log entry per order line.
 */
class OrderDetailCostSeeder extends Seeder
{
    public function run(): void
    {
        $latestCost = '(select product_variants.cost from product_variants where product_variants.id = order_details.product_variant_id)';

        $updated = DB::table('order_details')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('product_variants')
                    ->whereColumn('product_variants.id', 'order_details.product_variant_id')
                    ->whereColumn('product_variants.cost', '<>', 'order_details.cost');
            })
            ->update([
                'cost' => DB::raw($latestCost),
                'updated_at' => now(),
            ]);

        $this->command?->info("Updated the cost on {$updated} order line(s) to the latest product variant cost.");
    }
}
