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
            'disease_data' => $this->whenLoaded('diseaseData', function () {
                return $this->diseaseData->map(fn ($ud) => [
                    'disease_slug' => $ud->disease?->slug,
                    'disease_name' => $ud->disease?->name,
                    'field_values' => $ud->field_values,
                    'updated_at' => $ud->updated_at?->toIso8601String(),
                ]);
            }),
            'active_digital_twin' => $this->whenLoaded('activeDigitalTwin', fn () => new DigitalTwinResource($this->activeDigitalTwin)),
            'simulations' => SimulationResource::collection($this->whenLoaded('simulations')),
        ];
    }
}
