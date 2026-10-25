<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskCompletionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'task' => new TaskResource($this->whenLoaded('task')),
            'is_done' => $this->is_done,
            'remarks' => $this->remarks,
            'completed_at' => $this->completed_at,
        ];
    }
}
