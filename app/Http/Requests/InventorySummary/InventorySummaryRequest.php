<?php

namespace App\Http\Requests\InventorySummary;

use Illuminate\Foundation\Http\FormRequest;

class InventorySummaryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Leave out for every active branch.
            'branch' => ['nullable', 'exists:branches,uuid'],
            // Business day to report as of; defaults to today.
            'date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
