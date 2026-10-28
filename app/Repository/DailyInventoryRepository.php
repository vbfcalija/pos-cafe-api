<?php

namespace App\Repository;

use App\Enums\DailyInventoryStatus;
use App\Interface\Repository\DailyInventoryRepositoryInterface;
use App\Models\DailyInventory;
use App\Models\DailyInventoryUser;
use App\Models\InventoryCount;
use App\Models\InventoryItem;
use App\Support\ActivityLogger;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class DailyInventoryRepository implements DailyInventoryRepositoryInterface
{
    /**
     * Lines in sheet order (item lines by creation, "Other" lines after
     * them), counters in the order they were added.
     */
    private function relations(): array
    {
        return [
            'branch',
            'counts' => fn ($query) => $query->orderByRaw('inventory_item_id IS NULL')->orderBy('id'),
            'dailyInventoryUsers' => fn ($query) => $query->orderBy('id'),
            'dailyInventoryUsers.user',
        ];
    }

    public function findMany(object $payload, string $sortField, string $sortOrder)
    {
        $pageLength = min((int) ($payload->page_length ?? config('services.paginate')), 500);

        return DailyInventory::filter($payload->all())
            ->with($this->relations())
            ->orderBy($sortField, $sortOrder)
            ->orderBy('id', 'desc')
            ->paginate($pageLength);
    }

    public function findByUuid(string $uuid)
    {
        return DailyInventory::with($this->relations())->where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Returns the branch's sheet for the day, creating it if needed. While
     * it's pending, any newly active item is added, and an empty Begin is
     * filled from the same item's Ending on the branch's previous sheet.
     * Numbers someone has already typed are never overwritten.
     */
    public function generate(int $branchId, string $date): DailyInventory
    {
        return DB::transaction(function () use ($branchId, $date) {
            // whereDate: SQLite (mobile build, tests) stores dates with a time part.
            $sheet = DailyInventory::where('branch_id', $branchId)->whereDate('date', $date)->first();

            if (! $sheet) {
                try {
                    $sheet = DailyInventory::create([
                        'branch_id' => $branchId,
                        'date' => $date,
                        'status' => DailyInventoryStatus::Pending,
                    ]);
                } catch (UniqueConstraintViolationException) {
                    $sheet = DailyInventory::where('branch_id', $branchId)->whereDate('date', $date)->firstOrFail();
                }
            }

            if ($sheet->isVerified()) {
                return $sheet;
            }

            $previousEndings = $this->previousEndings($branchId, $date);
            $existing = $sheet->counts()->whereNotNull('inventory_item_id')->pluck('inventory_item_id')->all();

            InventoryItem::where('is_active', true)
                ->whereNotIn('id', $existing)
                ->orderBy('id')
                ->each(function (InventoryItem $item) use ($sheet, $previousEndings) {
                    $sheet->counts()->create([
                        'inventory_item_id' => $item->id,
                        'item_name' => $item->name,
                        'unit' => $item->unit,
                        'begin' => $previousEndings[$item->id] ?? null,
                    ]);
                });

            // Yesterday's closing count may have been entered after this sheet was
            // made. Carrying it over is bookkeeping, so it isn't activity-logged.
            ActivityLogger::withoutLogging(fn () => $sheet->counts()
                ->whereNull('begin')
                ->whereNotNull('inventory_item_id')
                ->get()
                ->each(function (InventoryCount $count) use ($previousEndings) {
                    if (isset($previousEndings[$count->inventory_item_id])) {
                        $count->begin = $previousEndings[$count->inventory_item_id];
                        $count->save();
                    }
                }));

            return $sheet;
        });
    }

    public function updateStatus(DailyInventory $dailyInventory, string $status)
    {
        $dailyInventory->status = $status;
        $dailyInventory->save();

        return $dailyInventory;
    }

    public function findCount(DailyInventory $dailyInventory, string $countUuid)
    {
        return InventoryCount::where('daily_inventory_id', $dailyInventory->id)
            ->where('uuid', $countUuid)
            ->firstOrFail();
    }

    public function updateCount(DailyInventory $dailyInventory, string $countUuid, object $payload)
    {
        $count = $this->findCount($dailyInventory, $countUuid);

        // has() rather than ??, so a value can be cleared by sending it empty.
        foreach (['begin', 'ending'] as $field) {
            if ($payload->has($field)) {
                $count->{$field} = $payload->{$field} === '' ? null : $payload->{$field};
            }
        }

        if ($payload->has('remarks')) {
            $count->remarks = $payload->remarks ?: null;
        }

        // Only "Other" lines have a typed name and unit.
        if ($count->isOther()) {
            $count->item_name = $payload->item_name ?? $count->item_name;
            $count->unit = $payload->unit ?? $count->unit;
        }

        $count->save();

        return $count;
    }

    public function addOtherCount(DailyInventory $dailyInventory, object $payload)
    {
        return $dailyInventory->counts()->create([
            'inventory_item_id' => null,
            'item_name' => $payload->item_name,
            'unit' => $payload->unit,
            'begin' => $payload->begin === '' ? null : $payload->begin,
            'ending' => $payload->ending === '' ? null : $payload->ending,
            'remarks' => $payload->remarks ?: null,
        ]);
    }

    /**
     * Makes the counters exactly these users.
     */
    public function syncUsers(DailyInventory $dailyInventory, array $userIds)
    {
        DB::transaction(function () use ($dailyInventory, $userIds) {
            DailyInventoryUser::where('daily_inventory_id', $dailyInventory->id)
                ->whereNotIn('user_id', $userIds)
                ->get()
                ->each->delete(); // one by one, so each removal is logged

            foreach ($userIds as $userId) {
                DailyInventoryUser::firstOrCreate([
                    'daily_inventory_id' => $dailyInventory->id,
                    'user_id' => $userId,
                ]);
            }
        });
    }

    /**
     * item id => Ending on the branch's most recent sheet before $date.
     */
    private function previousEndings(int $branchId, string $date): array
    {
        $previous = DailyInventory::where('branch_id', $branchId)
            ->whereDate('date', '<', $date)
            ->orderBy('date', 'desc')
            ->first();

        if (! $previous) {
            return [];
        }

        return $previous->counts()
            ->whereNotNull('inventory_item_id')
            ->whereNotNull('ending')
            ->pluck('ending', 'inventory_item_id')
            ->all();
    }
}
