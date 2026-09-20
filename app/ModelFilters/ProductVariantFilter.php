<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class ProductVariantFilter extends ModelFilter
{
    public $relations = [];

    public function name($name)
    {
        return $this->where('name', 'LIKE', "%{$name}%");
    }

    public function search($search)
    {
        return $this->where(function ($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('sku', 'LIKE', "%{$search}%")
                        ->orWhere('barcode', 'LIKE', "%{$search}%");
                });
        });
    }
}
