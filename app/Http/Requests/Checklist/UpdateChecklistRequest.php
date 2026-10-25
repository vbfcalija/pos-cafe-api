<?php

namespace App\Http\Requests\Checklist;

use App\Enums\ChecklistStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChecklistRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Local café time, "HH:mm"; empty clears it.
            'time_started' => ['nullable', 'date_format:H:i'],
            'time_finished' => ['nullable', 'date_format:H:i'],
            'status' => ['sometimes', 'required', Rule::enum(ChecklistStatus::class)],
        ];
    }
}
