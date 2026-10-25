<?php

namespace App\Models;

use App\Enums\ChecklistType;
use App\Traits\UsesUuid;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use Filterable, HasFactory, UsesUuid;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'type',
    ];

    protected $casts = [
        'type' => ChecklistType::class,
    ];

    public function completions(): HasMany
    {
        return $this->hasMany(TaskCompletion::class);
    }
}
