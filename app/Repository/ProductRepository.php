<?php

namespace App\Repository;

use App\Interface\Repository\ProductRepositoryInterface;
use App\Models\Category;
use App\Models\Product;
use App\Models\TaxRate;

class ProductRepository implements ProductRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder)
    {
        return Product::filter($payload->all())
            ->with(['category', 'taxRate'])
            ->orderBy($sortField, $sortOrder)
            ->paginate(config('services.paginate'));
    }

    public function findByUuid(string $uuid)
    {
        return Product::with(['category', 'taxRate'])->where('uuid', $uuid)->firstOrFail();
    }

    public function create(object $payload)
    {
        $product = new Product;
        $product->sku = $payload->sku;
        $product->barcode = $payload->barcode ?? null;
        $product->name = $payload->name;
        $product->price = $payload->price;
        $product->cost = $payload->cost;
        $product->color = $payload->color ?? null;
        $product->category_id = Category::where('uuid', $payload->category_uuid)->firstOrFail()->id;
        $product->tax_rate_id = TaxRate::where('uuid', $payload->tax_rate_uuid)->firstOrFail()->id;
        $product->save();

        return $product->fresh();
    }

    public function update(object $payload, string $uuid)
    {
        $product = Product::where('uuid', $uuid)->firstOrFail();
        $product->sku = $payload->sku ?? $product->sku;
        $product->barcode = $payload->barcode ?? $product->barcode;
        $product->name = $payload->name ?? $product->name;
        $product->price = $payload->price ?? $product->price;
        $product->cost = $payload->cost ?? $product->cost;
        $product->color = $payload->color ?? $product->color;

        if ($payload->category_uuid) {
            $product->category_id = Category::where('uuid', $payload->category_uuid)->firstOrFail()->id;
        }

        if ($payload->tax_rate_uuid) {
            $product->tax_rate_id = TaxRate::where('uuid', $payload->tax_rate_uuid)->firstOrFail()->id;
        }

        $product->save();

        return $product->fresh();
    }

    public function delete(string $uuid)
    {
        $product = Product::where('uuid', $uuid)->firstOrFail();
        $product->delete(); // rejected by the DB FK constraint if a variant still uses it
    }
}
