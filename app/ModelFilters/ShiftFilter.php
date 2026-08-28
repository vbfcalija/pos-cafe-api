<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class ShiftFilter extends ModelFilter
{
    public $relations = [];

    public function name($name)
    {
        return $this->where('name', 'LIKE', "%{$name}%");
    }

    public function search($search)
    {
        return $this->where('name', 'LIKE', "%{$search}%");
    }
}
