<?php

namespace App\Http\Resources;

use App\Enums\ChecklistType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChecklistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'date' => $this->date?->format('Y-m-d'),
            'time_started' => $this->time_started,
            'time_finished' => $this->time_finished,
            'status' => $this->status,
            'users' => ChecklistUserResource::collection($this->whenLoaded('checklistUsers')),
            // Listed in task order (creation order), which is the order the sheet is printed in.
            'tasks' => TaskCompletionResource::collection($this->whenLoaded(
                'taskCompletions',
                fn () => $this->taskCompletions->sortBy('task_id')->values()
            )),
            'progress' => $this->whenLoaded('taskCompletions', fn () => [
                'opener' => $this->progressFor(ChecklistType::Opener),
                'closer' => $this->progressFor(ChecklistType::Closer),
            ]),
            'created_at' => $this->created_at,
        ];
    }

    private function progressFor(ChecklistType $type): array
    {
        $tasks = $this->taskCompletions->filter(fn ($completion) => $completion->task?->type === $type);

        return [
            'done' => $tasks->where('is_done', true)->count(),
            'total' => $tasks->count(),
        ];
    }
}
