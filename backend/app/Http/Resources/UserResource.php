<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_admin' => $this->is_admin,
            'created_at' => $this->created_at?->toIso8601String(),
            'health_profile' => $this->whenLoaded('healthProfile', fn () => new HealthProfileResource($this->healthProfile)),
            'disease_diabetes' => $this->whenLoaded('diseaseDiabetes', fn () => new DiabetesResource($this->diseaseDiabetes)),
            'disease_pcod' => $this->whenLoaded('diseasePcod', fn () => new PcodResource($this->diseasePcod)),
            'active_digital_twin' => $this->whenLoaded('activeDigitalTwin', fn () => new DigitalTwinResource($this->activeDigitalTwin)),
            'simulations' => SimulationResource::collection($this->whenLoaded('simulations')),
        ];
    }
}
