<?php

namespace App\Models;

use App\Enums\DiscountType;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    use HasFactory, UsesUuid;

    protected $fillable = [
        'uuid',
        'name',
        'type',
        'value',
    ];

    protected $casts = [
        'type' => DiscountType::class,
        'value' => 'decimal:2',
    ];

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }
}
