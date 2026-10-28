<?php

namespace App\Interface\Repository;

use App\Models\DailyInventory;

interface DailyInventoryRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder);

    public function findByUuid(string $uuid);

    public function generate(int $branchId, string $date): DailyInventory;

    public function updateStatus(DailyInventory $dailyInventory, string $status);

    public function findCount(DailyInventory $dailyInventory, string $countUuid);

    public function updateCount(DailyInventory $dailyInventory, string $countUuid, object $payload);

    public function addOtherCount(DailyInventory $dailyInventory, object $payload);

    public function syncUsers(DailyInventory $dailyInventory, array $userIds);
}
