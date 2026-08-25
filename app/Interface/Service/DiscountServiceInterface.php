<?php

namespace App\Interface\Service;

interface DiscountServiceInterface
{
    public function findDiscounts(object $payload);

    public function findDiscount(string $uuid);

    public function createDiscount(object $payload);

    public function updateDiscount(object $payload, string $uuid);

    public function deleteDiscount(string $uuid);
}
