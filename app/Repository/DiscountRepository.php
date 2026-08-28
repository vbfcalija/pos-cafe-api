<?php

namespace App\Repository;

use App\Interface\Repository\DiscountRepositoryInterface;
use App\Models\Discount;

class DiscountRepository implements DiscountRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder)
    {
        return Discount::filter($payload->all())
            ->orderBy($sortField, $sortOrder)
            ->paginate(config('services.paginate'));
    }

    public function findByUuid(string $uuid)
    {
        return Discount::where('uuid', $uuid)->firstOrFail();
    }

    public function create(object $payload)
    {
        $discount = new Discount;
        $discount->name = $payload->name;
        $discount->type = $payload->type;
        $discount->value = $payload->value;
        $discount->save();

        return $discount->fresh();
    }

    public function update(object $payload, string $uuid)
    {
        $discount = Discount::where('uuid', $uuid)->firstOrFail();
        $discount->name = $payload->name ?? $discount->name;
        $discount->type = $payload->type ?? $discount->type;
        $discount->value = $payload->value ?? $discount->value;
        $discount->save();

        return $discount->fresh();
    }

    public function delete(string $uuid)
    {
        $discount = Discount::where('uuid', $uuid)->firstOrFail();
        $discount->delete(); // rejected by the DB FK constraint if an order line still uses it
    }
}
