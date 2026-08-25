<?php

namespace App\Interface\Service;

interface OrderServiceInterface
{
    public function createOrder(object $payload);
}
