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
            'date' => $this->date,
            'name' => $this->name,
            'starting_cash' => $this->starting_cash,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
