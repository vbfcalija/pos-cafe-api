<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\Traits\UsesUuid;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use Filterable, HasFactory, LogsActivity, UsesUuid;

    // Orders are never deleted; a refund is an update.
    protected static array $activityEvents = ['created', 'updated'];

    protected $fillable = [
        'uuid',
        'shift_id',
        'customer_id',
        'order_no',
        'date',
        'user_id',
        'refunded_at',
        'refunded_by_user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'refunded_at' => 'datetime',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by_user_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function activityLabel(): string
    {
        return '#' . $this->order_no;
    }

    public function activityEventName(string $event, array $changes): string
    {
        return $event === 'updated' && filled($changes['refunded_at']['new'] ?? null) ? 'refunded' : $event;
    }

    public function activityDescription(string $event, array $changes): ?string
    {
        return match ($this->activityEventName($event, $changes)) {
            'created' => "Created order {$this->activityLabel()}",
            'refunded' => "Refunded order {$this->activityLabel()}",
            default => null,
        };
    }
}
