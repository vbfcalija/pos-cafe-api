<?php

namespace App\Http\Requests\Shift;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShiftRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'branch_uuid' => ['sometimes', 'required', 'exists:branches,uuid'],
            'date' => ['sometimes', 'required', 'date'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'starting_cash' => ['sometimes', 'required', 'integer', 'min:0'],
            'is_open' => ['sometimes', 'boolean'],
        ];
    }
}
