<?php

namespace App\Interface\Service;

interface TaxRateServiceInterface
{
    public function findTaxRates(object $payload);

    public function findTaxRate(string $uuid);

    public function createTaxRate(object $payload);

    public function updateTaxRate(object $payload, string $uuid);

    public function deleteTaxRate(string $uuid);
}
