<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\Traits\UsesUuid;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use Filterable, HasFactory, LogsActivity, UsesUuid;

    protected $fillable = [
        'uuid',
        'name',
        'unit',
        'low_stock_threshold',
        'is_active',
    ];

    protected $casts = [
        'low_stock_threshold' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function counts(): HasMany
    {
        return $this->hasMany(InventoryCount::class);
    }
}
