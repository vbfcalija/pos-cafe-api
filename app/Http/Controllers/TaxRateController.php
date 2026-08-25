<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaxRate\StoreTaxRateRequest;
use App\Http\Requests\TaxRate\UpdateTaxRateRequest;
use App\Interface\Service\TaxRateServiceInterface;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    private $taxRateService;

    public function __construct(TaxRateServiceInterface $taxRateService)
    {
        $this->taxRateService = $taxRateService;
    }

    public function index(Request $request)
    {
        return $this->taxRateService->findTaxRates($request);
    }

    public function store(StoreTaxRateRequest $request)
    {
        return $this->taxRateService->createTaxRate($request);
    }

    public function show(string $uuid)
    {
        return $this->taxRateService->findTaxRate($uuid);
    }

    public function update(UpdateTaxRateRequest $request, string $uuid)
    {
        return $this->taxRateService->updateTaxRate($request, $uuid);
    }

    public function destroy(string $uuid)
    {
        return $this->taxRateService->deleteTaxRate($uuid);
    }
}
