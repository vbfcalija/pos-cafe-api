<?php

namespace App\Http\Requests\Order;

use App\Enums\PaymentMethod;
use App\Models\Shift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
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

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $shift = Shift::where('uuid', $this->shift_uuid)->first();

                if (! $shift) {
                    return;
                }

                if (! $shift->is_open) {
                    $validator->errors()->add('shift_uuid', 'An open shift is required before creating an order.');
                }

                if ($shift->user_id !== $this->user()->id) {
                    $validator->errors()->add('shift_uuid', 'The selected shift does not belong to the current user.');
                }
            },
        ];
    }
}
