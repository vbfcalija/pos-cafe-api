<?php

namespace App\Http\Requests\Order;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'branch_uuid' => ['required', 'exists:branches,uuid'],
            'shift_uuid' => ['required', 'exists:shifts,uuid'],
            'customer_uuid' => ['nullable', 'exists:customers,uuid'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_variant_uuid' => ['required', 'exists:product_variants,uuid'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'lines.*.discount_uuid' => ['nullable', 'exists:discounts,uuid'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'payments.*.reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}
