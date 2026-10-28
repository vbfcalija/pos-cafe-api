<?php

namespace App\Service;

use App\Interface\Service\InventorySummaryServiceInterface;
use App\Models\Branch;
use App\Models\InventoryCount;
use App\Models\InventoryItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * What needs restocking, per branch, from the inventory counts.
 *
 * For each active item it takes the latest Ending counted at the branch on
 * or before the chosen day (so a half-finished sheet today doesn't hide
 * yesterday's count of an item), then sorts items into:
 *
 *   out          Ending is 0
 *   low          Ending is at or below the item's low stock level
 *   ok           above its level
 *   untracked    counted, but no low stock level set
 *   not_counted  never counted at this branch
 */
class InventorySummaryService implements InventorySummaryServiceInterface
{
    public function summarize(object $payload)
    {
        $date = $payload->date
            ? Carbon::parse($payload->date)->format('Y-m-d')
            : now(config('app.business_timezone'))->format('Y-m-d');

        $branches = Branch::where('is_active', true)
            ->when($payload->branch, fn ($query) => $query->where('uuid', $payload->branch))
            ->orderBy('name')
            ->get();

        $items = InventoryItem::where('is_active', true)->orderBy('id')->get();

        return response()->json([
            'data' => [
                'date' => $date,
                'branches' => $branches->map(fn (Branch $branch) => $this->summarizeBranch($branch, $items, $date))->values(),
            ],
        ]);
    }

    private function summarizeBranch(Branch $branch, Collection $items, string $date): array
    {
        $latest = $this->latestEndings($branch->id, $date);
        $rows = $items->map(function (InventoryItem $item) use ($latest) {
            $count = $latest->get($item->id);
            $ending = $count ? (float) $count->ending : null;
            $threshold = $item->low_stock_threshold === null ? null : (float) $item->low_stock_threshold;

            return [
                'item_uuid' => $item->uuid,
                'name' => $item->name,
                'unit' => $item->unit,
                'ending' => $ending,
                'low_stock_threshold' => $threshold,
                'counted_on' => $count?->sheet_date ? Carbon::parse($count->sheet_date)->format('Y-m-d') : null,
                'status' => match (true) {
                    $ending === null => 'not_counted',
                    $ending <= 0 => 'out',
                    $threshold === null => 'untracked',
                    $ending <= $threshold => 'low',
                    default => 'ok',
                },
            ];
        });

        // Out of stock first, then the lowest relative to its level.
        $restock = $rows->whereIn('status', ['out', 'low'])
            ->sortBy(fn ($row) => [
                $row['status'] === 'out' ? 0 : 1,
                $row['low_stock_threshold'] ? $row['ending'] / $row['low_stock_threshold'] : 0,
                $row['name'],
            ])
            ->values();

        return [
            'branch' => ['uuid' => $branch->uuid, 'name' => $branch->name],
            'last_counted_on' => $rows->pluck('counted_on')->filter()->max(),
            'restock' => $restock,
            'totals' => [
                'out' => $rows->where('status', 'out')->count(),
                'low' => $rows->where('status', 'low')->count(),
                'ok' => $rows->where('status', 'ok')->count(),
                'untracked' => $rows->where('status', 'untracked')->count(),
                'not_counted' => $rows->where('status', 'not_counted')->count(),
            ],
        ];
    }

    /**
     * inventory_item_id => its most recent counted line at the branch on or
     * before $date (with that sheet's date as sheet_date).
     */
    private function latestEndings(int $branchId, string $date): Collection
    {
        return InventoryCount::query()
            ->join('daily_inventories', 'daily_inventories.id', '=', 'inventory_counts.daily_inventory_id')
            ->where('daily_inventories.branch_id', $branchId)
            ->whereDate('daily_inventories.date', '<=', $date)
            ->whereNotNull('inventory_counts.inventory_item_id')
            ->whereNotNull('inventory_counts.ending')
            ->orderBy('daily_inventories.date', 'desc')
            ->orderBy('inventory_counts.id', 'desc')
            ->get(['inventory_counts.inventory_item_id', 'inventory_counts.ending', 'daily_inventories.date as sheet_date'])
            ->unique('inventory_item_id')
            ->keyBy('inventory_item_id');
    }
}
