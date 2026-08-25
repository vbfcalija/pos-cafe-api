<?php

namespace App\Traits;

trait SortingTraits
{
    public function sortField($payload, $defaultColumn)
    {
        $sortField = $payload->sortField;

        // check if sortField value is undefined and set it to defaultColumn to sort
        if (! $sortField) {
            return $defaultColumn;
        } else {
            return $sortField;
        }
    }

    public function sortOrder($payload, $defaultOrder = '')
    {
        $sortOrder = $payload->sortOrder;

        // check if sortOrder value is ascend and set it to asc
        if ($sortOrder == 'ascend') {
            return $sortOrder = 'asc';
        }
        // check if sortOrder value is descend and set it to desc
        elseif ($sortOrder == 'descend') {
            return $sortOrder = 'desc';
        }
        // sortOrder value is undefined and set it to $defaultOrder or asc
        else {
            if (! empty($defaultOrder)) {
                return $sortOrder = $defaultOrder;
            }

            return $sortOrder = 'asc';
        }
    }
}
