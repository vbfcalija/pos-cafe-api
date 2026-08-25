<?php

namespace App\Service;

use App\Http\Resources\TaxRateResource;
use App\Interface\Repository\TaxRateRepositoryInterface;
use App\Interface\Service\TaxRateServiceInterface;
use App\Traits\SortingTraits;

class TaxRateService implements TaxRateServiceInterface
{
    use SortingTraits;

    private $taxRateRepository;

    public function __construct(TaxRateRepositoryInterface $taxRateRepository)
    {
        $this->taxRateRepository = $taxRateRepository;
    }

    public function findTaxRates(object $payload)
    {
        $sortField = $this->sortField($payload, 'name');
        $sortOrder = $this->sortOrder($payload, 'asc');

        $taxRates = $this->taxRateRepository->findMany($payload, $sortField, $sortOrder);

        return TaxRateResource::collection($taxRates);
    }

    public function findTaxRate(string $uuid)
    {
        $taxRate = $this->taxRateRepository->findByUuid($uuid);

        return new TaxRateResource($taxRate);
    }

    public function createTaxRate(object $payload)
    {
        $taxRate = $this->taxRateRepository->create($payload);

        return new TaxRateResource($taxRate);
    }

    public function updateTaxRate(object $payload, string $uuid)
    {
        $taxRate = $this->taxRateRepository->update($payload, $uuid);

        return new TaxRateResource($taxRate);
    }

    public function deleteTaxRate(string $uuid)
    {
        $this->taxRateRepository->delete($uuid);

        return response()->json([
            'message' => 'Tax rate deleted successfully',
        ], 200);
    }
}
