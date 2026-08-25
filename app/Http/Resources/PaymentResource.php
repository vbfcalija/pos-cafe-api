<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'date' => $this->date,
            'reference' => $this->reference,
            'payment_method' => $this->payment_method,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
