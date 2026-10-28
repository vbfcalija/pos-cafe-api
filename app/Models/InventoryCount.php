<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryCount extends Model
{
    use HasFactory, LogsActivity, UsesUuid;

    // A day's lines are created in bulk with the sheet; entering counts is what matters.
    protected static array $activityEvents = ['updated', 'deleted'];

    protected $fillable = [
        'uuid',
        'daily_inventory_id',
        'inventory_item_id',
        'item_name',
        'unit',
        'begin',
        'ending',
        'remarks',
    ];

    protected $casts = [
        'begin' => 'decimal:2',
        'ending' => 'decimal:2',
    ];

    public function dailyInventory(): BelongsTo
    {
        return $this->belongsTo(DailyInventory::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function isOther(): bool
    {
        return $this->inventory_item_id === null;
    }

    public function activityLabel(): string
    {
        return (string) $this->item_name;
    }

    public function activityDescription(string $event, array $changes): ?string
    {
        $sheet = $this->dailyInventory?->activityLabel() ?? 'an inventory';

        if ($event === 'deleted') {
            return "Removed “{$this->item_name}” from inventory “{$sheet}”";
        }

        $parts = [];
        foreach (['begin' => 'begin', 'ending' => 'ending'] as $field => $label) {
            if (array_key_exists($field, $changes)) {
                $parts[] = "{$label} " . ($changes[$field]['new'] ?? 'cleared');
            }
        }
        if (array_key_exists('remarks', $changes)) {
            $parts[] = 'remark';
        }

        return "Updated “{$this->item_name}” on inventory “{$sheet}”" . ($parts ? ': ' . implode(', ', $parts) : '');
    }
}
