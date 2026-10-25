<?php

namespace App\Repository;

use App\Interface\Repository\TaskRepositoryInterface;
use App\Models\Task;

class TaskRepository implements TaskRepositoryInterface
{
    public function findMany(object $payload, string $sortField, string $sortOrder)
    {
        $pageLength = min((int) ($payload->page_length ?? config('services.paginate')), 500);

        return Task::filter($payload->all())
            ->orderBy($sortField, $sortOrder)
            ->orderBy('id')
            ->paginate($pageLength);
    }

    public function findByUuid(string $uuid)
    {
        return Task::where('uuid', $uuid)->firstOrFail();
    }

    public function create(object $payload)
    {
        $task = new Task;
        $task->name = $payload->name;
        $task->description = $payload->description ?: null;
        $task->type = $payload->type;
        $task->save();

        return $task->fresh();
    }

    public function update(object $payload, string $uuid)
    {
        $task = Task::where('uuid', $uuid)->firstOrFail();
        $task->name = $payload->name ?? $task->name;
        $task->type = $payload->type ?? $task->type;

        // has() rather than ??, so a description can be cleared by sending it empty.
        if ($payload->has('description')) {
            $task->description = $payload->description ?: null;
        }

        $task->save();

        return $task->fresh();
    }

    public function delete(string $uuid)
    {
        $task = Task::where('uuid', $uuid)->firstOrFail();
        $task->delete(); // rejected by the DB FK constraint once a checklist has used it
    }
}
