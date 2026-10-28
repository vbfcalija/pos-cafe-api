<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryItem\StoreInventoryItemRequest;
use App\Http\Requests\InventoryItem\UpdateInventoryItemRequest;
use App\Interface\Service\InventoryItemServiceInterface;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    private $inventoryItemService;

    public function __construct(InventoryItemServiceInterface $inventoryItemService)
    {
        $this->inventoryItemService = $inventoryItemService;
    }

    public function index(Request $request)
    {
        return $this->inventoryItemService->findInventoryItems($request);
    }

    public function store(StoreInventoryItemRequest $request)
    {
        return $this->inventoryItemService->createInventoryItem($request);
    }

    public function show(string $uuid)
    {
        return $this->inventoryItemService->findInventoryItem($uuid);
    }

    public function update(UpdateInventoryItemRequest $request, string $uuid)
    {
        return $this->inventoryItemService->updateInventoryItem($request, $uuid);
    }

    public function destroy(string $uuid)
    {
        return $this->inventoryItemService->deleteInventoryItem($uuid);
    }
}
