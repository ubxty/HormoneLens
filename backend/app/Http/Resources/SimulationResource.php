<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SimulationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type?->value,
            'input_data' => $this->input_data,
            'original_risk_score' => (float) $this->original_risk_score,
            'simulated_risk_score' => (float) $this->simulated_risk_score,
            'risk_change' => (float) $this->risk_change,
            'risk_category_before' => $this->risk_category_before?->value,
            'risk_category_after' => $this->risk_category_after?->value,
            'rag_explanation' => $this->rag_explanation,
            'rag_confidence' => $this->rag_confidence ? (float) $this->rag_confidence : null,
            'results' => $this->results,
            'alerts' => AlertResource::collection($this->whenLoaded('alerts')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
