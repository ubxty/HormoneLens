<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'period_days' => $this['period_days'] ?? 30,
            'period_start' => $this['period_start'] ?? null,
            'period_end' => $this['period_end'] ?? null,
            'new_users' => $this['new_users'] ?? 0,
            'total_simulations' => $this['total_simulations'] ?? 0,
            'simulations_in_period' => $this['simulations_in_period'] ?? 0,
            'risk_distribution' => $this['risk_distribution'] ?? [],
            'average_risk_score' => $this['average_risk_score'] ?? 0,
            'daily_risk_scores' => $this['daily_risk_scores'] ?? [],
            'daily_simulations' => $this['daily_simulations'] ?? [],
            'daily_alerts_by_severity' => $this['daily_alerts_by_severity'] ?? [],
        ];
    }
}
