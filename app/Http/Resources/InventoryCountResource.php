<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryCountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'is_other' => $this->inventory_item_id === null,
            'item_name' => $this->item_name,
            'unit' => $this->unit,
            'begin' => $this->begin === null ? null : (float) $this->begin,
            'ending' => $this->ending === null ? null : (float) $this->ending,
            'remarks' => $this->remarks,
            // The item's current low-stock level, for flagging on the sheet.
            'low_stock_threshold' => $this->relationLoaded('inventoryItem') && $this->inventoryItem?->low_stock_threshold !== null
                ? (float) $this->inventoryItem->low_stock_threshold
                : null,
        ];
    }
}
