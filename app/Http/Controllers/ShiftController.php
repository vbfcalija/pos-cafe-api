<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shift\StoreShiftRequest;
use App\Http\Requests\Shift\UpdateShiftRequest;
use App\Interface\Service\ShiftServiceInterface;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    private $shiftService;

    public function __construct(ShiftServiceInterface $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    public function index(Request $request)
    {
        return $this->shiftService->findShifts($request);
    }

    public function store(StoreShiftRequest $request)
    {
        return $this->shiftService->createShift($request);
    }

    public function show(string $uuid)
    {
        return $this->shiftService->findShift($uuid);
    }

    public function update(UpdateShiftRequest $request, string $uuid)
    {
        return $this->shiftService->updateShift($request, $uuid);
    }

    public function destroy(string $uuid)
    {
        return $this->shiftService->deleteShift($uuid);
    }
}
