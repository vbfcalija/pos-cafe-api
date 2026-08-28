<?php

namespace App\Repository;

use App\Interface\Repository\ProductVariantRepositoryInterface;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductVariantRepository implements ProductVariantRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder)
    {
        return ProductVariant::filter($payload->all())
            ->with('product')
            ->orderBy($sortField, $sortOrder)
            ->paginate(config('services.paginate'));
    }

    public function findByUuid(string $uuid)
    {
        return ProductVariant::with('product')->where('uuid', $uuid)->firstOrFail();
    }

    public function create(object $payload)
    {
        $variant = new ProductVariant;
        $variant->product_id = Product::where('uuid', $payload->product_uuid)->firstOrFail()->id;
        $variant->name = $payload->name;
        $variant->price = $payload->price;
        $variant->cost = $payload->cost;
        $variant->is_active = $payload->is_active ?? true;
        $variant->save();

        return $variant->fresh();
    }

    public function update(object $payload, string $uuid)
    {
        $variant = ProductVariant::where('uuid', $uuid)->firstOrFail();

        if ($payload->product_uuid) {
            $variant->product_id = Product::where('uuid', $payload->product_uuid)->firstOrFail()->id;
        }

        $variant->name = $payload->name ?? $variant->name;
        $variant->price = $payload->price ?? $variant->price;
        $variant->cost = $payload->cost ?? $variant->cost;
        $variant->is_active = $payload->is_active ?? $variant->is_active;
        $variant->save();

        return $variant->fresh();
    }

    public function delete(string $uuid)
    {
        $variant = ProductVariant::where('uuid', $uuid)->firstOrFail();
        $variant->delete(); // rejected by the DB FK constraint if an order line still uses it
    }
}
