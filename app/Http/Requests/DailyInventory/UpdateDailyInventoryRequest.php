<?php

namespace App\Http\Requests\DailyInventory;

use App\Enums\DailyInventoryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDailyInventoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(DailyInventoryStatus::class)],
        ];
    }
}
