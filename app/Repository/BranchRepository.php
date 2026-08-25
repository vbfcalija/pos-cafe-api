<?php

namespace App\Repository;

use App\Interface\Repository\BranchRepositoryInterface;
use App\Models\Branch;

class BranchRepository implements BranchRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder)
    {
        return Branch::orderBy($sortField, $sortOrder)
            ->paginate(config('services.paginate'));
    }

    public function findByUuid(string $uuid)
    {
        return Branch::where('uuid', $uuid)->firstOrFail();
    }

    public function create(object $payload)
    {
        $branch = new Branch;
        $branch->name = $payload->name;
        $branch->address = $payload->address;
        $branch->phone = $payload->phone ?? null;
        $branch->alternate_phone = $payload->alternate_phone ?? null;
        $branch->email = $payload->email ?? null;
        $branch->is_active = $payload->is_active ?? true;
        $branch->save();

        return $branch->fresh();
    }

    public function update(object $payload, string $uuid)
    {
        $branch = Branch::where('uuid', $uuid)->firstOrFail();
        $branch->name = $payload->name ?? $branch->name;
        $branch->address = $payload->address ?? $branch->address;
        $branch->phone = $payload->phone ?? $branch->phone;
        $branch->alternate_phone = $payload->alternate_phone ?? $branch->alternate_phone;
        $branch->email = $payload->email ?? $branch->email;
        $branch->is_active = $payload->is_active ?? $branch->is_active;
        $branch->save();

        return $branch->fresh();
    }

    public function delete(string $uuid)
    {
        $branch = Branch::where('uuid', $uuid)->firstOrFail();
        $branch->delete(); // rejected by the DB FK constraint if an order/shift still uses it
    }
}
