<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DigitalTwinResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'metabolic_health_score' => (float) $this->metabolic_health_score,
            'insulin_resistance_score' => (float) $this->insulin_resistance_score,
            'sleep_score' => (float) $this->sleep_score,
            'stress_score' => (float) $this->stress_score,
            'diet_score' => (float) $this->diet_score,
            'overall_risk_score' => (float) $this->overall_risk_score,
            'risk_category' => $this->risk_category?->value,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
