<?php

namespace App\Http\Requests\Discount;

use App\Enums\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDiscountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', Rule::enum(DiscountType::class)],
            'value' => ['sometimes', 'required', 'numeric', 'min:0'],
        ];
    }
}
