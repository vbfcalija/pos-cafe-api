<?php

namespace App\ModelFilters;

use Carbon\Carbon;
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

    public function dateFrom($date)
    {
        $start = Carbon::parse($date, config('app.business_timezone'))
            ->startOfDay()
            ->utc();

        return $this->where('created_at', '>=', $start);
    }

    public function dateTo($date)
    {
        $end = Carbon::parse($date, config('app.business_timezone'))
            ->endOfDay()
            ->utc();

        return $this->where('created_at', '<=', $end);
    }
}
