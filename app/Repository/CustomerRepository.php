<?php

namespace App\Repository;

use App\Interface\Repository\CustomerRepositoryInterface;
use App\Models\Customer;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder)
    {
        return Customer::orderBy($sortField, $sortOrder)
            ->paginate(config('services.paginate'));
    }

    public function findByUuid(string $uuid)
    {
        return Customer::where('uuid', $uuid)->firstOrFail();
    }

    public function create(object $payload)
    {
        $customer = new Customer;
        $customer->name = $payload->name;
        $customer->tin = $payload->tin ?? null;
        $customer->address = $payload->address ?? null;
        $customer->contact_number = $payload->contact_number ?? null;
        $customer->save();

        return $customer->fresh();
    }

    public function update(object $payload, string $uuid)
    {
        $customer = Customer::where('uuid', $uuid)->firstOrFail();
        $customer->name = $payload->name ?? $customer->name;
        $customer->tin = $payload->tin ?? $customer->tin;
        $customer->address = $payload->address ?? $customer->address;
        $customer->contact_number = $payload->contact_number ?? $customer->contact_number;
        $customer->save();

        return $customer->fresh();
    }

    public function delete(string $uuid)
    {
        $customer = Customer::where('uuid', $uuid)->firstOrFail();
        $customer->delete(); // rejected by the DB FK constraint if an order still uses it
    }
}
