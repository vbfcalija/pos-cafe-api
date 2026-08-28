<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class DiscountFilter extends ModelFilter
{
    public $relations = [];

    public function name($name)
    {
        return $this->where('name', 'LIKE', "%{$name}%");
    }

    public function type($type)
    {
        return $this->where('type', $type);
    }

    public function search($search)
    {
        return $this->where('name', 'LIKE', "%{$search}%");
    }
}
