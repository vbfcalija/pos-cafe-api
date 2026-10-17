<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:50'],
            'category_uuid' => ['sometimes', 'required', 'exists:categories,uuid'],
            'tax_rate_uuid' => ['sometimes', 'required', 'exists:tax_rates,uuid'],
        ];
    }
}
