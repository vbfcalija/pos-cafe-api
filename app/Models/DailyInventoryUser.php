<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyInventoryUser extends Model
{
    use HasFactory, LogsActivity, UsesUuid;

    protected static array $activityEvents = ['created', 'deleted'];

    protected $fillable = [
        'uuid',
        'daily_inventory_id',
        'user_id',
    ];

    public function dailyInventory(): BelongsTo
    {
        return $this->belongsTo(DailyInventory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activityDescription(string $event, array $changes): ?string
    {
        $person = $this->user?->activityLabel() ?? 'someone';
        $sheet = $this->dailyInventory?->activityLabel() ?? 'an inventory';

        return $event === 'created'
            ? "Added {$person} as counter on inventory “{$sheet}”"
            : "Removed {$person} as counter from inventory “{$sheet}”";
    }
}
