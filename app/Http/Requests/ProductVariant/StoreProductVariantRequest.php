<?php

namespace App\Http\Requests\ProductVariant;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductVariantRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_uuid' => ['required', 'exists:products,uuid'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', 'unique:product_variants,sku'],
            'barcode' => ['nullable', 'string', 'max:255', 'unique:product_variants,barcode'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'is_track_stock' => ['sometimes', 'boolean'],
            'current_stocks' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
