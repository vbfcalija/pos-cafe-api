<?php

namespace App\Http\Controllers;

use App\Http\Requests\DailyInventory\StoreDailyInventoryRequest;
use App\Http\Requests\DailyInventory\StoreInventoryCountRequest;
use App\Http\Requests\DailyInventory\SyncDailyInventoryUsersRequest;
use App\Http\Requests\DailyInventory\UpdateDailyInventoryRequest;
use App\Http\Requests\DailyInventory\UpdateInventoryCountRequest;
use App\Interface\Service\DailyInventoryServiceInterface;
use Illuminate\Http\Request;

class DailyInventoryController extends Controller
{
    private $dailyInventoryService;

    public function __construct(DailyInventoryServiceInterface $dailyInventoryService)
    {
        $this->dailyInventoryService = $dailyInventoryService;
    }

    public function index(Request $request)
    {
        return $this->dailyInventoryService->findDailyInventories($request);
    }

    /**
     * Generates (or returns the existing) sheet for a branch and day.
     */
    public function store(StoreDailyInventoryRequest $request)
    {
        return $this->dailyInventoryService->generateDailyInventory($request);
    }

    public function show(string $uuid)
    {
        return $this->dailyInventoryService->findDailyInventory($uuid);
    }

    public function update(UpdateDailyInventoryRequest $request, string $uuid)
    {
        return $this->dailyInventoryService->updateDailyInventory($request, $uuid);
    }

    public function updateCount(UpdateInventoryCountRequest $request, string $uuid, string $countUuid)
    {
        return $this->dailyInventoryService->updateCount($request, $uuid, $countUuid);
    }

    public function addOtherCount(StoreInventoryCountRequest $request, string $uuid)
    {
        return $this->dailyInventoryService->addOtherCount($request, $uuid);
    }

    public function removeOtherCount(string $uuid, string $countUuid)
    {
        return $this->dailyInventoryService->removeOtherCount($uuid, $countUuid);
    }

    public function syncUsers(SyncDailyInventoryUsersRequest $request, string $uuid)
    {
        return $this->dailyInventoryService->syncUsers($request, $uuid);
    }
}
