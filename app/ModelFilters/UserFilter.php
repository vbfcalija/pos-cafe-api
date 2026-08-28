<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class UserFilter extends ModelFilter
{
    public $relations = [];

    public function name($name)
    {
        return $this->where(function ($query) use ($name) {
            $query->where('firstname', 'LIKE', "%{$name}%")
                ->orWhere('lastname', 'LIKE', "%{$name}%");
        });
    }

    public function email($email)
    {
        return $this->where('email', 'LIKE', "%{$email}%");
    }

    public function search($search)
    {
        return $this->where(function ($query) use ($search) {
            $query->where('firstname', 'LIKE', "%{$search}%")
                ->orWhere('lastname', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
        });
    }
}
