<?php

namespace App\Http\Requests\DailyInventory;

use Illuminate\Foundation\Http\FormRequest;

/**
 * An "Other" line: something not on the item list, named by whoever counts.
 */
class StoreInventoryCountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'item_name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'begin' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'ending' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
