<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class InventoryItemFilter extends ModelFilter
{
    public $relations = [];

    public function isActive($isActive)
    {
        return $this->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
    }

    public function search($search)
    {
        return $this->where(function ($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('unit', 'LIKE', "%{$search}%");
        });
    }
}
