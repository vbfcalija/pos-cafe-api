<?php

namespace App\Repository;

use App\Interface\Repository\ShiftRepositoryInterface;
use App\Models\Branch;
use App\Models\Shift;

class ShiftRepository implements ShiftRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder)
    {
        $pageLength = min((int) ($payload->page_length ?? config('services.paginate')), 500);

        return Shift::filter($payload->all())
            ->with(['branch', 'user'])
            ->orderBy($sortField, $sortOrder)
            ->paginate($pageLength);
    }

    public function findByUuid(string $uuid)
    {
        return Shift::with(['branch', 'user'])->where('uuid', $uuid)->firstOrFail();
    }

    public function create(object $payload)
    {
        $shift = new Shift;
        $shift->branch_id = Branch::where('uuid', $payload->branch_uuid)->firstOrFail()->id;
        $shift->date = $payload->date;
        $shift->name = $payload->name;
        $shift->starting_cash = $payload->starting_cash;
        $shift->user_id = $payload->user()->id;
        $shift->is_open = $payload->is_open ?? true;
        $shift->save();

        return $shift->fresh();
    }

    public function update(object $payload, string $uuid)
    {
        $shift = Shift::where('uuid', $uuid)->firstOrFail();
        if ($payload->branch_uuid) {
            $shift->branch_id = Branch::where('uuid', $payload->branch_uuid)->firstOrFail()->id;
        }
        $shift->date = $payload->date ?? $shift->date;
        $shift->name = $payload->name ?? $shift->name;
        $shift->starting_cash = $payload->starting_cash ?? $shift->starting_cash;
        $shift->is_open = $payload->is_open ?? $shift->is_open;
        $shift->save();

        return $shift->fresh();
    }

    public function delete(string $uuid)
    {
        $shift = Shift::where('uuid', $uuid)->firstOrFail();
        $shift->delete(); // rejected by the DB FK constraint if an order still uses it
    }
}
