<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'price' => $this->price,
            'cost' => $this->cost,
            'is_active' => $this->is_active,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
