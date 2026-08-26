<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'quantity' => $this->quantity,
            'price' => (float) $this->price,
            'cost' => (float) $this->cost,
            'tax_percentage' => (float) $this->tax_percentage,
            'product_variant' => new ProductVariantResource($this->whenLoaded('productVariant')),
            'discount' => new DiscountResource($this->whenLoaded('discount')),
        ];
    }
}
