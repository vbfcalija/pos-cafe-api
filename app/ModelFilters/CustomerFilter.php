<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class CustomerFilter extends ModelFilter
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
                ->orWhere('tin', 'LIKE', "%{$search}%")
                ->orWhere('contact_number', 'LIKE', "%{$search}%");
        });
    }
}
