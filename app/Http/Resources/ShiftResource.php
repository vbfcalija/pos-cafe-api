<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'date' => $this->date,
            'name' => $this->name,
            'starting_cash' => $this->starting_cash,
            'user' => new UserResource($this->whenLoaded('user')),
            'computer_id' => $this->computer_id,
            'is_open' => $this->is_open,
        ];
    }
}
