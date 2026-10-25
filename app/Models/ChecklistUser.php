<?php

namespace App\Models;

use App\Enums\ChecklistType;
use App\Traits\LogsActivity;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistUser extends Model
{
    use HasFactory, LogsActivity, UsesUuid;

    protected static array $activityEvents = ['created', 'deleted'];

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

    public function activityDescription(string $event, array $changes): ?string
    {
        $person = $this->user?->activityLabel() ?? 'someone';
        $checklist = $this->checklist?->activityLabel() ?? 'a checklist';
        $role = $this->type?->value ?? 'barista';

        return $event === 'created'
            ? "Added {$person} as {$role} on checklist “{$checklist}”"
            : "Removed {$person} as {$role} from checklist “{$checklist}”";
    }
}
