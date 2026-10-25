<?php

namespace App\Http\Requests\Checklist;

use App\Enums\ChecklistType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChecklistUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_uuid' => ['required', 'exists:users,uuid'],
            'type' => ['required', Rule::enum(ChecklistType::class)],
        ];
    }
}
