<?php

namespace App\Interface\Repository;

interface OrderRepositoryInterface
{
    public function create(object $payload);

    public function addLine($order, array $line);

    public function addPayment($order, array $payment);

    public function nextOrderNo();
}
