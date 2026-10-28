<?php

namespace App\Interface\Service;

interface DailyInventoryServiceInterface
{
    public function findDailyInventories(object $payload);

    public function findDailyInventory(string $uuid);

    public function generateDailyInventory(object $payload);

    public function updateDailyInventory(object $payload, string $uuid);

    public function updateCount(object $payload, string $uuid, string $countUuid);

    public function addOtherCount(object $payload, string $uuid);

    public function removeOtherCount(string $uuid, string $countUuid);

    public function syncUsers(object $payload, string $uuid);
}
