<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'weight' => (float) $this->weight,
            'height' => (float) $this->height,
            'avg_sleep_hours' => (float) $this->avg_sleep_hours,
            'stress_level' => $this->stress_level?->value,
            'physical_activity' => $this->physical_activity?->value,
            'eating_habits' => $this->eating_habits,
            'water_intake' => (float) $this->water_intake,
            'disease_type' => $this->disease_type?->value,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
