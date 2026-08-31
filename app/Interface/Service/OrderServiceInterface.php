<?php

namespace App\Interface\Service;

interface OrderServiceInterface
{
    public function findOrders(object $payload);

    public function findOrder(string $uuid);

    public function createOrder(object $payload);
}
