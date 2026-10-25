<?php

namespace App\Models;

use App\Traits\UsesUuid;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use Filterable, UsesUuid;

    protected $fillable = [
        'uuid',
        'user_id',
        'event',
        'description',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'device_id',
        'device_type',
        'device_model',
        'platform',
        'browser',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
