<?php

namespace App\Http\Requests\Checklist;

use Illuminate\Foundation\Http\FormRequest;

class StoreChecklistRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'branch_uuid' => ['required', 'exists:branches,uuid'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
