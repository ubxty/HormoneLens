<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'daily_risk_scores' => $this['daily_risk_scores'] ?? [],
            'daily_simulations' => $this['daily_simulations'] ?? [],
            'daily_alerts_by_severity' => $this['daily_alerts_by_severity'] ?? [],
        ];
    }
}
