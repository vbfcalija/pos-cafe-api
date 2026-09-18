<?php

namespace App\Interface\Service;

interface OrderServiceInterface
{
    public function findOrders(object $payload);

    public function findOrder(string $uuid);

    public function createOrder(object $payload);

    public function printReceipt(string $uuid);

    public function downloadReceipt(string $uuid);

    public function receiptEscPos(string $uuid);

    public function refundOrder(string $uuid, object $payload);

    public function updatePayment(string $uuid, string $paymentUuid, object $payload);
}
