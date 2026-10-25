<?php

namespace App\Repository;

use App\Enums\ChecklistStatus;
use App\Interface\Repository\ChecklistRepositoryInterface;
use App\Models\Checklist;
use App\Models\ChecklistUser;
use App\Models\Task;
use App\Models\TaskCompletion;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class ChecklistRepository implements ChecklistRepositoryInterface
{

    /**
     * Baristas in the order they were added; tasks are put in sheet order by
     * ChecklistResource.
     */
    private function relations(): array
    {
        return [
            'branch',
            'checklistUsers' => fn ($query) => $query->orderBy('id'),
            'checklistUsers.user',
            'taskCompletions.task',
        ];
    }

    public function findMany(object $payload, string $sortField, string $sortOrder)
    {
        $pageLength = min((int) ($payload->page_length ?? config('services.paginate')), 500);

        return Checklist::filter($payload->all())
            ->with($this->relations())
            ->orderBy($sortField, $sortOrder)
            ->orderBy('id', 'desc')
            ->paginate($pageLength);
    }

    public function findByUuid(string $uuid)
    {
        return Checklist::with($this->relations())->where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Returns the branch's checklist for the day, creating it if needed.
     * Idempotent: while the checklist is still pending, any task added since
     * it was generated is added to it; tasks already on it are left alone,
     * so ticks and remarks are never reset.
     */
    public function generate(int $branchId, string $date): Checklist
    {
        return DB::transaction(function () use ($branchId, $date) {
            // whereDate rather than firstOrCreate's plain `date = ?`: SQLite (the
            // mobile build, tests) stores the date with a 00:00:00 time part, so
            // an exact match would miss the existing row.
            $checklist = Checklist::where('branch_id', $branchId)->whereDate('date', $date)->first();

            if (! $checklist) {
                try {
                    $checklist = Checklist::create([
                        'branch_id' => $branchId,
                        'date' => $date,
                        'status' => ChecklistStatus::Pending,
                    ]);
                } catch (UniqueConstraintViolationException) {
                    // Another request (or the scheduler) created it a moment ago.
                    $checklist = Checklist::where('branch_id', $branchId)->whereDate('date', $date)->firstOrFail();
                }
            }

            if (! $checklist->isVerified()) {
                $existing = $checklist->taskCompletions()->pluck('task_id');

                Task::whereNotIn('id', $existing)->orderBy('id')->each(function (Task $task) use ($checklist) {
                    $checklist->taskCompletions()->create(['task_id' => $task->id, 'is_done' => false]);
                });
            }

            return $checklist;
        });
    }

    public function update(Checklist $checklist, object $payload)
    {
        if ($payload->has('time_started')) {
            $checklist->time_started = $this->toDateTime($checklist, $payload->time_started);
        }

        if ($payload->has('time_finished')) {
            $finished = $this->toDateTime($checklist, $payload->time_finished);

            // A closing shift that ends after midnight belongs to the next calendar day.
            if ($finished && $checklist->time_started && $finished->lt($checklist->time_started)) {
                $finished->addDay();
            }

            $checklist->time_finished = $finished;
        }

        if ($payload->status) {
            $checklist->status = $payload->status;
        }

        $checklist->save();

        return $checklist;
    }

    public function updateTaskCompletion(Checklist $checklist, string $taskCompletionUuid, object $payload)
    {
        $completion = TaskCompletion::where('checklist_id', $checklist->id)
            ->where('uuid', $taskCompletionUuid)
            ->firstOrFail();

        if ($payload->has('is_done')) {
            $isDone = (bool) $payload->is_done;

            if ($isDone !== $completion->is_done) {
                $completion->is_done = $isDone;
                $completion->completed_at = $isDone ? now() : null;
            }
        }

        if ($payload->has('remarks')) {
            $completion->remarks = $payload->remarks ?: null;
        }

        $completion->save();

        // The day starts with the first tick and finishes with the last one,
        // unless someone has already entered those times by hand.
        $checklist->refresh();
        $done = $checklist->taskCompletions()->where('is_done', true)->count();
        $total = $checklist->taskCompletions()->count();

        if ($done > 0 && ! $checklist->time_started) {
            $checklist->time_started = now();
        }

        if ($total > 0 && $done === $total && ! $checklist->time_finished) {
            $checklist->time_finished = now();
        }

        $checklist->save();

        return $completion;
    }

    public function addUser(Checklist $checklist, int $userId, string $type)
    {
        return ChecklistUser::firstOrCreate([
            'checklist_id' => $checklist->id,
            'type' => $type,
            'user_id' => $userId,
        ]);
    }

    public function removeUser(Checklist $checklist, string $checklistUserUuid)
    {
        ChecklistUser::where('checklist_id', $checklist->id)
            ->where('uuid', $checklistUserUuid)
            ->firstOrFail()
            ->delete();
    }

    /**
     * Makes the openers (or closers) exactly these users: adds the new ones,
     * removes the ones no longer listed, and leaves everyone else untouched.
     */
    public function syncUsers(Checklist $checklist, string $type, array $userIds)
    {
        DB::transaction(function () use ($checklist, $type, $userIds) {
            ChecklistUser::where('checklist_id', $checklist->id)
                ->where('type', $type)
                ->whereNotIn('user_id', $userIds)
                ->delete();

            foreach ($userIds as $userId) {
                $this->addUser($checklist, $userId, $type);
            }
        });
    }

    /**
     * "HH:mm" entered in the café's local time on the checklist's date,
     * stored in UTC like every other timestamp.
     */
    private function toDateTime(Checklist $checklist, ?string $time): ?Carbon
    {
        if (! $time) {
            return null;
        }

        return Carbon::parse(
            $checklist->date->format('Y-m-d') . ' ' . $time,
            config('app.business_timezone')
        )->utc();
    }
}
