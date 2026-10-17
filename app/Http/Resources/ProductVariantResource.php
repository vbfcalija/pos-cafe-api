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
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'price' => (float) $this->price,
            'cost' => (float) $this->cost,
            'is_active' => $this->is_active,
            'is_track_stock' => $this->is_track_stock,
            'current_stocks' => $this->current_stocks,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
