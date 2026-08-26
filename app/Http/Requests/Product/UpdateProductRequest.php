<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sku' => ['sometimes', 'required', 'string', 'max:255', 'unique:products,sku,'.$this->route('uuid').',uuid'],
            'barcode' => ['nullable', 'string', 'max:255', 'unique:products,barcode,'.$this->route('uuid').',uuid'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'cost' => ['sometimes', 'required', 'numeric', 'min:0'],
            'color' => ['nullable', 'string', 'max:50'],
            'category_uuid' => ['sometimes', 'required', 'exists:categories,uuid'],
            'tax_rate_uuid' => ['sometimes', 'required', 'exists:tax_rate,uuid'],
        ];
    }
}
