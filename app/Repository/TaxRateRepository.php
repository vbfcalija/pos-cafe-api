<?php

namespace App\Repository;

use App\Interface\Repository\TaxRateRepositoryInterface;
use App\Models\TaxRate;

class TaxRateRepository implements TaxRateRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder)
    {
        $pageLength = min((int) ($payload->page_length ?? config('services.paginate')), 500);

        return TaxRate::filter($payload->all())
            ->orderBy($sortField, $sortOrder)
            ->paginate($pageLength);
    }

    public function findByUuid(string $uuid)
    {
        return TaxRate::where('uuid', $uuid)->firstOrFail();
    }

    public function create(object $payload)
    {
        $taxRate = new TaxRate;
        $taxRate->name = $payload->name;
        $taxRate->percentage = $payload->percentage;
        $taxRate->save();

        return $taxRate->fresh();
    }

    public function update(object $payload, string $uuid)
    {
        $taxRate = TaxRate::where('uuid', $uuid)->firstOrFail();
        $taxRate->name = $payload->name ?? $taxRate->name;
        $taxRate->percentage = $payload->percentage ?? $taxRate->percentage;
        $taxRate->save();

        return $taxRate->fresh();
    }

    public function delete(string $uuid)
    {
        $taxRate = TaxRate::where('uuid', $uuid)->firstOrFail();
        $taxRate->delete(); // rejected by the DB FK constraint if a product still uses it
    }
}
