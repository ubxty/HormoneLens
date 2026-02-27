<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'type' => $this->type?->value,
            'title' => $this->title,
            'message' => $this->message,
            'severity' => $this->severity?->value,
            'is_read' => $this->is_read,
            'simulation_id' => $this->simulation_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
