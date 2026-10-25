<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'event' => $this->event,
            'description' => $this->description,
            'user' => new UserResource($this->whenLoaded('user')),
            'subject_type' => $this->subject_type ? class_basename($this->subject_type) : null,
            'properties' => $this->properties,
            'ip_address' => $this->ip_address,
            'device_id' => $this->device_id,
            'device_type' => $this->device_type,
            'device_model' => $this->device_model,
            'platform' => $this->platform,
            'browser' => $this->browser,
            'user_agent' => $this->user_agent,
            'created_at' => $this->created_at,
        ];
    }
}
