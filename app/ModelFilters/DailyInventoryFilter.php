<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class DailyInventoryFilter extends ModelFilter
{
    public $relations = [];

    public function branch($branchUuid)
    {
        return $this->whereHas('branch', fn ($query) => $query->where('uuid', $branchUuid));
    }

    public function status($status)
    {
        return $this->where('status', $status);
    }

    // `date` is the business day itself, so it's compared as-is.
    public function dateFrom($date)
    {
        return $this->whereDate('date', '>=', $date);
    }

    public function dateTo($date)
    {
        return $this->whereDate('date', '<=', $date);
    }
}
