<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PcodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cycle_regularity' => $this->cycle_regularity?->value,
            'avg_cycle_length_days' => $this->avg_cycle_length_days,
            'excess_facial_body_hair' => $this->excess_facial_body_hair,
            'acne_oily_skin' => $this->acne_oily_skin,
            'hair_thinning' => $this->hair_thinning,
            'weight_gain_difficulty_losing' => $this->weight_gain_difficulty_losing,
            'mood_swings_anxiety' => $this->mood_swings_anxiety,
            'dark_skin_patches' => $this->dark_skin_patches,
            'fatigue_frequency' => $this->fatigue_frequency?->value,
            'sleep_disturbances' => $this->sleep_disturbances?->value,
            'sugar_cravings' => $this->sugar_cravings?->value,
            'insulin_resistance_diagnosed' => $this->insulin_resistance_diagnosed,
        ];
    }
}
