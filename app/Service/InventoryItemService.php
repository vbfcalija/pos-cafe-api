<?php

namespace App\Service;

use App\Http\Resources\InventoryItemResource;
use App\Interface\Repository\InventoryItemRepositoryInterface;
use App\Interface\Service\InventoryItemServiceInterface;
use App\Traits\SortingTraits;

class InventoryItemService implements InventoryItemServiceInterface
{
    use SortingTraits;

    private $inventoryItemRepository;

    public function __construct(InventoryItemRepositoryInterface $inventoryItemRepository)
    {
        $this->inventoryItemRepository = $inventoryItemRepository;
    }

    public function findInventoryItems(object $payload)
    {
        $sortField = $this->sortField($payload, 'id');
        $sortOrder = $this->sortOrder($payload, 'asc');

        return InventoryItemResource::collection(
            $this->inventoryItemRepository->findMany($payload, $sortField, $sortOrder)
        );
    }

    public function findInventoryItem(string $uuid)
    {
        return new InventoryItemResource($this->inventoryItemRepository->findByUuid($uuid));
    }

    public function createInventoryItem(object $payload)
    {
        return new InventoryItemResource($this->inventoryItemRepository->create($payload));
    }

    public function updateInventoryItem(object $payload, string $uuid)
    {
        return new InventoryItemResource($this->inventoryItemRepository->update($payload, $uuid));
    }

    public function deleteInventoryItem(string $uuid)
    {
        $this->inventoryItemRepository->delete($uuid);

        return response()->json([
            'message' => 'Success.',
        ], 200);
    }
}
