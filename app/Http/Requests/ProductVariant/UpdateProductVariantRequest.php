<?php

namespace App\Http\Requests\ProductVariant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    public function rules(): array
    {
        $productVariantUuid = $this->route('product_variant');

        return [
            'product_uuid' => ['sometimes', 'required', 'exists:products,uuid'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sku' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('product_variants', 'sku')->ignore($productVariantUuid, 'uuid'),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('product_variants', 'barcode')->ignore($productVariantUuid, 'uuid'),
            ],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'cost' => ['sometimes', 'required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'is_track_stock' => ['sometimes', 'boolean'],
            'current_stocks' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
