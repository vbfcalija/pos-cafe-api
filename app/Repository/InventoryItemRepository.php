<?php

namespace App\Repository;

use App\Interface\Repository\InventoryItemRepositoryInterface;
use App\Models\InventoryItem;

class InventoryItemRepository implements InventoryItemRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder)
    {
        $pageLength = min((int) ($payload->page_length ?? config('services.paginate')), 500);

        return InventoryItem::filter($payload->all())
            ->orderBy($sortField, $sortOrder)
            ->orderBy('id')
            ->paginate($pageLength);
    }

    public function findByUuid(string $uuid)
    {
        return InventoryItem::where('uuid', $uuid)->firstOrFail();
    }

    public function create(object $payload)
    {
        $item = new InventoryItem;
        $item->name = $payload->name;
        $item->unit = $payload->unit;
        $item->low_stock_threshold = $this->threshold($payload->low_stock_threshold);
        $item->is_active = $payload->is_active ?? true;
        $item->save();

        return $item->fresh();
    }

    public function update(object $payload, string $uuid)
    {
        $item = InventoryItem::where('uuid', $uuid)->firstOrFail();
        $item->name = $payload->name ?? $item->name;
        $item->unit = $payload->unit ?? $item->unit;

        // has() rather than ??, so the level can be cleared by sending it empty.
        if ($payload->has('low_stock_threshold')) {
            $item->low_stock_threshold = $this->threshold($payload->low_stock_threshold);
        }

        $item->is_active = $payload->is_active ?? $item->is_active;
        $item->save();

        return $item->fresh();
    }

    private function threshold($value)
    {
        return $value === null || $value === '' ? null : $value;
    }

    public function delete(string $uuid)
    {
        $item = InventoryItem::where('uuid', $uuid)->firstOrFail();
        $item->delete(); // rejected by the DB FK constraint once a count has used it; deactivate instead
    }
}
