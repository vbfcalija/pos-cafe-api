<?php

namespace App\Http\Requests\DailyInventory;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyInventoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'branch_uuid' => ['required', 'exists:branches,uuid'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
