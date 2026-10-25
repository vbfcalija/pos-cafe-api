<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskCompletion extends Model
{
    use HasFactory, LogsActivity, UsesUuid;

    // A day's task rows are created in bulk with the checklist; ticks and remarks are what matter.
    protected static array $activityEvents = ['updated'];

    protected array $activityIgnore = ['completed_at'];

    protected $fillable = [
        'uuid',
        'checklist_id',
        'task_id',
        'is_done',
        'remarks',
        'completed_at',
    ];

    protected $casts = [
        'is_done' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function activityLabel(): string
    {
        return (string) ($this->task?->name ?? 'task');
    }

    public function activityEventName(string $event, array $changes): string
    {
        if (isset($changes['is_done'])) {
            return $changes['is_done']['new'] ? 'task_done' : 'task_undone';
        }

        return $event;
    }

    public function activityDescription(string $event, array $changes): ?string
    {
        $checklist = $this->checklist?->activityLabel() ?? 'a checklist';

        return match ($this->activityEventName($event, $changes)) {
            'task_done' => "Ticked “{$this->activityLabel()}” on checklist “{$checklist}”",
            'task_undone' => "Unticked “{$this->activityLabel()}” on checklist “{$checklist}”",
            default => "Updated the remark on “{$this->activityLabel()}” ({$checklist})",
        };
    }
}
