<?php

namespace App\Models;

use App\Enums\ChecklistStatus;
use App\Traits\UsesUuid;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Checklist extends Model
{
    use Filterable, HasFactory, UsesUuid;

    protected $fillable = [
        'uuid',
        'branch_id',
        'date',
        'time_started',
        'time_finished',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'time_started' => 'datetime',
        'time_finished' => 'datetime',
        'status' => ChecklistStatus::class,
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function taskCompletions(): HasMany
    {
        return $this->hasMany(TaskCompletion::class);
    }

    public function checklistUsers(): HasMany
    {
        return $this->hasMany(ChecklistUser::class);
    }

    public function isVerified(): bool
    {
        return $this->status === ChecklistStatus::Verified;
    }
}
