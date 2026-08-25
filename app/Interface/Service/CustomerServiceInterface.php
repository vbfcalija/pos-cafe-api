<?php

namespace App\Interface\Service;

interface CustomerServiceInterface
{
    public function findCustomers(object $payload);

    public function findCustomer(string $uuid);

    public function createCustomer(object $payload);

    public function updateCustomer(object $payload, string $uuid);

    public function deleteCustomer(string $uuid);
}
