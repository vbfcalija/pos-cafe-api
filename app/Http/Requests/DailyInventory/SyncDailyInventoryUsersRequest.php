<?php

namespace App\Http\Requests\DailyInventory;

use Illuminate\Foundation\Http\FormRequest;

class SyncDailyInventoryUsersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // The full list of who counted; an empty list clears it.
            'user_uuids' => ['present', 'array'],
            'user_uuids.*' => ['string', 'distinct', 'exists:users,uuid'],
        ];
    }
}
