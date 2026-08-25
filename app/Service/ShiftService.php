<?php

namespace App\Service;

use App\Http\Resources\ShiftResource;
use App\Interface\Repository\ShiftRepositoryInterface;
use App\Interface\Service\ShiftServiceInterface;
use App\Traits\SortingTraits;

class ShiftService implements ShiftServiceInterface
{
    use SortingTraits;

    private $shiftRepository;

    public function __construct(ShiftRepositoryInterface $shiftRepository)
    {
        $this->shiftRepository = $shiftRepository;
    }

    public function findShifts(object $payload)
    {
        $sortField = $this->sortField($payload, 'date');
        $sortOrder = $this->sortOrder($payload, 'desc');

        $shifts = $this->shiftRepository->findMany($payload, $sortField, $sortOrder);

        return ShiftResource::collection($shifts);
    }

    public function findShift(string $uuid)
    {
        $shift = $this->shiftRepository->findByUuid($uuid);

        return new ShiftResource($shift);
    }

    public function createShift(object $payload)
    {
        $shift = $this->shiftRepository->create($payload);

        return new ShiftResource($shift);
    }

    public function updateShift(object $payload, string $uuid)
    {
        $shift = $this->shiftRepository->update($payload, $uuid);

        return new ShiftResource($shift);
    }

    public function deleteShift(string $uuid)
    {
        $this->shiftRepository->delete($uuid);

        return response()->json([
            'message' => 'Shift deleted successfully',
        ], 200);
    }
}
