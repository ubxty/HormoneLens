<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiabetesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'avg_blood_sugar' => (float) $this->avg_blood_sugar,
            'family_history' => $this->family_history,
            'frequent_urination' => $this->frequent_urination?->value,
            'excessive_thirst' => $this->excessive_thirst?->value,
            'fatigue' => $this->fatigue?->value,
            'blurred_vision' => $this->blurred_vision?->value,
            'numbness_tingling' => $this->numbness_tingling,
            'slow_wound_healing' => $this->slow_wound_healing,
            'unexplained_weight_loss' => $this->unexplained_weight_loss,
            'sugar_cravings' => $this->sugar_cravings?->value,
            'energy_crashes_after_meals' => $this->energy_crashes_after_meals,
        ];
    }
}
