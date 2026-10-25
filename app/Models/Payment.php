<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Traits\LogsActivity;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, LogsActivity, UsesUuid;

    // Payments are created with their order (already logged); only later edits are worth recording.
    protected static array $activityEvents = ['updated'];

    protected $fillable = [
        'uuid',
        'date',
        'order_id',
        'reference',
        'payment_method',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'payment_method' => PaymentMethod::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activityDescription(string $event, array $changes): ?string
    {
        $order = $this->order ? "order #{$this->order->order_no}" : 'an order';

        if (isset($changes['payment_method'])) {
            return "Changed payment on {$order} from {$changes['payment_method']['old']} to {$changes['payment_method']['new']}";
        }

        return "Updated payment on {$order}";
    }
}
