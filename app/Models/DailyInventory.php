<?php

namespace App\Models;

use App\Enums\DailyInventoryStatus;
use App\Traits\LogsActivity;
use App\Traits\UsesUuid;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyInventory extends Model
{
    use Filterable, HasFactory, LogsActivity, UsesUuid;

    protected $fillable = [
        'uuid',
        'branch_id',
        'date',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'status' => DailyInventoryStatus::class,
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function counts(): HasMany
    {
        return $this->hasMany(InventoryCount::class);
    }

    public function dailyInventoryUsers(): HasMany
    {
        return $this->hasMany(DailyInventoryUser::class);
    }

    public function isVerified(): bool
    {
        return $this->status === DailyInventoryStatus::Verified;
    }

    public function activityLabel(): string
    {
        return trim(($this->branch?->name ?? 'Branch') . ' · ' . $this->date?->format('M j, Y'));
    }

    public function activitySubjectName(): string
    {
        return 'inventory';
    }

    public function activityEventName(string $event, array $changes): string
    {
        return match ($changes['status']['new'] ?? null) {
            'verified' => 'verified',
            'pending' => 'reopened',
            default => $event,
        };
    }

    public function activityDescription(string $event, array $changes): ?string
    {
        return match ($this->activityEventName($event, $changes)) {
            'verified' => "Verified inventory “{$this->activityLabel()}”",
            'reopened' => "Reopened inventory “{$this->activityLabel()}”",
            default => null,
        };
    }
}
