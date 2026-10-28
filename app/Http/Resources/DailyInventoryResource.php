<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyInventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'date' => $this->date?->format('Y-m-d'),
            'status' => $this->status,
            'users' => DailyInventoryUserResource::collection($this->whenLoaded('dailyInventoryUsers')),
            'counts' => InventoryCountResource::collection($this->whenLoaded('counts')),
            'progress' => $this->whenLoaded('counts', fn () => [
                'counted' => $this->counts->whereNotNull('ending')->count(),
                'total' => $this->counts->count(),
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
