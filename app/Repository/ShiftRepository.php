<?php

namespace App\Repository;

use App\Interface\Repository\ShiftRepositoryInterface;
use App\Models\Shift;

class ShiftRepository implements ShiftRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder)
    {
        return Shift::filter($payload->all())
            ->with('user')
            ->orderBy($sortField, $sortOrder)
            ->paginate(config('services.paginate'));
    }

    public function findByUuid(string $uuid)
    {
        return Shift::with('user')->where('uuid', $uuid)->firstOrFail();
    }

    public function create(object $payload)
    {
        $shift = new Shift;
        $shift->date = $payload->date;
        $shift->name = $payload->name;
        $shift->starting_cash = $payload->starting_cash;
        $shift->user_id = $payload->user()->id;
        $shift->save();

        return $shift->fresh();
    }

    public function update(object $payload, string $uuid)
    {
        $shift = Shift::where('uuid', $uuid)->firstOrFail();
        $shift->date = $payload->date ?? $shift->date;
        $shift->name = $payload->name ?? $shift->name;
        $shift->starting_cash = $payload->starting_cash ?? $shift->starting_cash;
        $shift->save();

        return $shift->fresh();
    }

    public function delete(string $uuid)
    {
        $shift = Shift::where('uuid', $uuid)->firstOrFail();
        $shift->delete(); // rejected by the DB FK constraint if an order still uses it
    }
}
