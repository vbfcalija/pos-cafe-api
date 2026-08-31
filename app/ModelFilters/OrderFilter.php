<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class OrderFilter extends ModelFilter
{
    public $relations = [];

    public function orderNo($orderNo)
    {
        return $this->where('order_no', 'LIKE', "%{$orderNo}%");
    }

    public function search($search)
    {
        return $this->where('order_no', 'LIKE', "%{$search}%");
    }
}
