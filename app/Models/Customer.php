<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, UsesUuid;

    protected $fillable = [
        'uuid',
        'name',
        'tin',
        'address',
        'contact_number',
        'payment_method',
    ];

    protected $casts = [
        'payment_method' => PaymentMethod::class,
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
