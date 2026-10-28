<?php

namespace App\Service;

use App\Enums\DailyInventoryStatus;
use App\Http\Resources\DailyInventoryResource;
use App\Interface\Repository\DailyInventoryRepositoryInterface;
use App\Interface\Service\DailyInventoryServiceInterface;
use App\Models\Branch;
use App\Models\DailyInventory;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Traits\SortingTraits;
use Carbon\Carbon;
use Illuminate\Http\Response;

class DailyInventoryService implements DailyInventoryServiceInterface
{
    use SortingTraits;

    private $dailyInventoryRepository;

    public function __construct(DailyInventoryRepositoryInterface $dailyInventoryRepository)
    {
        $this->dailyInventoryRepository = $dailyInventoryRepository;
    }

    public function findDailyInventories(object $payload)
    {
        $sortField = $this->sortField($payload, 'date');
        $sortOrder = $this->sortOrder($payload, 'desc');

        return DailyInventoryResource::collection(
            $this->dailyInventoryRepository->findMany($payload, $sortField, $sortOrder)
        );
    }

    public function findDailyInventory(string $uuid)
    {
        return new DailyInventoryResource($this->dailyInventoryRepository->findByUuid($uuid));
    }

    /**
     * Opens (creating if needed) a branch's sheet for a day — today in the
     * café's timezone unless a date is given.
     */
    public function generateDailyInventory(object $payload)
    {
        $branch = Branch::where('uuid', $payload->branch_uuid)->firstOrFail();
        $date = $payload->date
            ? Carbon::parse($payload->date)->format('Y-m-d')
            : now(config('app.business_timezone'))->format('Y-m-d');

        $sheet = $this->dailyInventoryRepository->generate($branch->id, $date);

        return $this->fresh($sheet->uuid);
    }

    /**
     * Verifies or reopens a sheet.
     */
    public function updateDailyInventory(object $payload, string $uuid)
    {
        $sheet = $this->dailyInventoryRepository->findByUuid($uuid);

        if ($payload->status === DailyInventoryStatus::Verified->value && ! $sheet->isVerified()
            && ($problem = $this->verificationProblem($sheet))) {
            return response()->json(['message' => $problem], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->dailyInventoryRepository->updateStatus($sheet, $payload->status);

        return $this->fresh($uuid);
    }

    public function updateCount(object $payload, string $uuid, string $countUuid)
    {
        $sheet = $this->dailyInventoryRepository->findByUuid($uuid);

        if ($sheet->isVerified()) {
            return $this->lockedResponse();
        }

        $this->dailyInventoryRepository->updateCount($sheet, $countUuid, $payload);

        return $this->fresh($uuid);
    }

    public function addOtherCount(object $payload, string $uuid)
    {
        $sheet = $this->dailyInventoryRepository->findByUuid($uuid);

        if ($sheet->isVerified()) {
            return $this->lockedResponse();
        }

        $count = $this->dailyInventoryRepository->addOtherCount($sheet, $payload);
        ActivityLogger::log('created', "Added “{$count->item_name}” to inventory “{$sheet->activityLabel()}”", $count, [
            'attributes' => ['item_name' => $count->item_name, 'unit' => $count->unit],
        ]);

        return $this->fresh($uuid);
    }

    /**
     * Only "Other" lines can be removed; item lines stay so every sheet
     * lists the full item list.
     */
    public function removeOtherCount(string $uuid, string $countUuid)
    {
        $sheet = $this->dailyInventoryRepository->findByUuid($uuid);

        if ($sheet->isVerified()) {
            return $this->lockedResponse();
        }

        $count = $this->dailyInventoryRepository->findCount($sheet, $countUuid);

        if (! $count->isOther()) {
            return response()->json([
                'message' => 'Only lines added under “Other” can be removed. To stop counting an item, deactivate it in Inventory items.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $count->delete();

        return $this->fresh($uuid);
    }

    public function syncUsers(object $payload, string $uuid)
    {
        $sheet = $this->dailyInventoryRepository->findByUuid($uuid);

        if ($sheet->isVerified()) {
            return $this->lockedResponse();
        }

        // Keep the order people were picked in (whereIn returns database order).
        $uuids = $payload->user_uuids ?? [];
        $idsByUuid = User::whereIn('uuid', $uuids)->pluck('id', 'uuid');
        $userIds = collect($uuids)->map(fn ($uuid) => $idsByUuid[$uuid] ?? null)->filter()->values()->all();
        $this->dailyInventoryRepository->syncUsers($sheet, $userIds);

        return $this->fresh($uuid);
    }

    /**
     * A sheet is verified once every line has an Ending count and at least
     * one person is named as having counted.
     */
    private function verificationProblem(DailyInventory $sheet): ?string
    {
        $missing = $sheet->counts->whereNull('ending')->count();

        if ($missing > 0) {
            return "{$missing} item" . ($missing === 1 ? ' has' : 's have') . ' no ending count yet. Enter 0 for anything that ran out.';
        }

        if ($sheet->dailyInventoryUsers->isEmpty()) {
            return 'Add who counted before verifying.';
        }

        return null;
    }

    private function fresh(string $uuid)
    {
        return new DailyInventoryResource($this->dailyInventoryRepository->findByUuid($uuid));
    }

    private function lockedResponse()
    {
        return response()->json([
            'message' => 'This inventory has been verified. Reopen it to make changes.',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
