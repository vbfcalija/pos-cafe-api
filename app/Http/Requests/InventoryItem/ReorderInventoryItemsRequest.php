<?php

namespace App\Http\Requests\InventoryItem;

use Illuminate\Foundation\Http\FormRequest;

class ReorderInventoryItemsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Every item, top to bottom, as it should be listed on the daily inventory.
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'distinct', 'exists:inventory_items,uuid'],
        ];
    }
}
