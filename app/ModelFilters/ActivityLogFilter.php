<?php

namespace App\ModelFilters;

use Carbon\Carbon;
use EloquentFilter\ModelFilter;

class ActivityLogFilter extends ModelFilter
{
    public $relations = [];

    public function user($userUuid)
    {
        return $this->whereHas('user', fn ($query) => $query->where('uuid', $userUuid));
    }

    public function event($event)
    {
        return $this->where('event', $event);
    }

    public function deviceId($deviceId)
    {
        return $this->where('device_id', $deviceId);
    }

    public function dateFrom($date)
    {
        return $this->where('created_at', '>=', Carbon::parse($date, config('app.business_timezone'))->startOfDay()->utc());
    }

    public function dateTo($date)
    {
        return $this->where('created_at', '<=', Carbon::parse($date, config('app.business_timezone'))->endOfDay()->utc());
    }

    public function search($search)
    {
        return $this->where(function ($query) use ($search) {
            $query->where('description', 'LIKE', "%{$search}%")
                ->orWhere('ip_address', 'LIKE', "%{$search}%")
                ->orWhere('device_id', 'LIKE', "%{$search}%");
        });
    }
}
