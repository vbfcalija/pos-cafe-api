<?php

namespace App\Models;

use App\Enums\ChecklistType;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistUser extends Model
{
    use HasFactory, UsesUuid;

    protected $fillable = [
        'uuid',
        'checklist_id',
        'type',
        'user_id',
    ];

    protected $casts = [
        'type' => ChecklistType::class,
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
