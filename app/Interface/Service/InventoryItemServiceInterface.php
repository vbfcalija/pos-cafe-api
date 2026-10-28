<?php

namespace App\Interface\Service;

interface InventoryItemServiceInterface
{
    public function findInventoryItems(object $payload);

    public function findInventoryItem(string $uuid);

    public function createInventoryItem(object $payload);

    public function updateInventoryItem(object $payload, string $uuid);

    public function deleteInventoryItem(string $uuid);
}
