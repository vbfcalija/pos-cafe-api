<?php

namespace App\Http\Requests\Checklist;

use App\Enums\ChecklistType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncChecklistUsersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(ChecklistType::class)],
            // The full list for that side; an empty list clears it.
            'user_uuids' => ['present', 'array'],
            'user_uuids.*' => ['string', 'distinct', 'exists:users,uuid'],
        ];
    }
}
