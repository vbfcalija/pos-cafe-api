<?php

namespace App\Http\Requests\Shift;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'branch_uuid' => ['required', 'exists:branches,uuid'],
            'date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'starting_cash' => ['required', 'integer', 'min:0'],
            'is_open' => ['sometimes', 'boolean'],
        ];
    }
}
