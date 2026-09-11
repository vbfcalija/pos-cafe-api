<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function rules(): array
    {
        $productUuid = $this->route('product');

        return [
            'sku' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'sku')->ignore($productUuid, 'uuid'),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'barcode')->ignore($productUuid, 'uuid'),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:50'],
            'category_uuid' => ['sometimes', 'required', 'exists:categories,uuid'],
            'tax_rate_uuid' => ['sometimes', 'required', 'exists:tax_rates,uuid'],
        ];
    }
}
