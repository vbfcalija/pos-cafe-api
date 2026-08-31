<?php

namespace App\Interface\Repository;

interface OrderRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder);

    public function findByUuid(string $uuid);

    public function create(object $payload);

    public function addLine($order, array $line);

    public function addPayment($order, array $payment);

    public function nextOrderNo();
}
