<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_users' => $this['total_users'],
            'new_users_7d' => $this['new_users_7d'],
            'simulations_today' => $this['simulations_today'],
            'simulations_week' => $this['simulations_week'],
            'simulations_total' => $this['simulations_total'],
            'risk_distribution' => $this['risk_distribution'],
            'unread_alerts' => $this['unread_alerts'],
            'avg_risk_score' => round($this['avg_risk_score'], 2),
        ];
    }
}
