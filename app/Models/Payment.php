<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, UsesUuid;

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
}
