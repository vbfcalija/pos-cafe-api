<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'name' => $this->name,
            'price' => $this->price,
            'cost' => $this->cost,
            'color' => $this->color,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'tax_rate' => new TaxRateResource($this->whenLoaded('taxRate')),
        ];
    }
}
