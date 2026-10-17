<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SKU and barcode identify the thing actually sold and scanned — a sized,
 * priced variant — not the product grouping above it. Existing values are
 * carried over rather than discarded:
 *
 * - A product with one variant hands its SKU and barcode to that variant
 *   unchanged.
 * - A product with several variants gives each one "<product SKU>-<variant
 *   name>" (e.g. "CF-LT-HP-8OZ") so SKUs stay unique, and its barcode goes to
 *   the first variant only, since a barcode can't be shared.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique()->after('name');
            $table->string('barcode')->nullable()->unique()->after('sku');
        });

        $used = [];

        DB::table('products')->orderBy('id')->each(function ($product) use (&$used) {
            $variants = DB::table('product_variants')
                ->where('product_id', $product->id)
                ->orderBy('id')
                ->get();

            foreach ($variants as $index => $variant) {
                $sku = $product->sku;

                if ($sku !== null && $variants->count() > 1) {
                    $suffix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $variant->name)) ?: (string) $variant->id;
                    $sku = "{$sku}-{$suffix}";
                }

                if ($sku !== null && isset($used[$sku])) {
                    $sku = "{$sku}-{$variant->id}";
                }

                if ($sku !== null) {
                    $used[$sku] = true;
                }

                DB::table('product_variants')->where('id', $variant->id)->update([
                    'sku' => $sku,
                    'barcode' => $index === 0 ? $product->barcode : null,
                ]);
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropUnique(['barcode']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sku', 'barcode']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique()->after('uuid');
            $table->string('barcode')->nullable()->unique()->after('sku');
        });

        // Best-effort restore: each product takes its first variant's values.
        DB::table('products')->orderBy('id')->each(function ($product) {
            $variant = DB::table('product_variants')
                ->where('product_id', $product->id)
                ->orderBy('id')
                ->first();

            DB::table('products')->where('id', $product->id)->update([
                'sku' => $variant?->sku,
                'barcode' => $variant?->barcode,
            ]);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropUnique(['barcode']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['sku', 'barcode']);
        });
    }
};
