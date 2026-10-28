<?php

namespace App\Http\Requests\DailyInventory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryCountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Empty clears the value.
            'begin' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'ending' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            // Only used on "Other" lines.
            'item_name' => ['sometimes', 'required', 'string', 'max:255'],
            'unit' => ['sometimes', 'required', 'string', 'max:50'],
        ];
    }
}
