<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiskDistributionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'low' => $this['low'] ?? 0,
            'moderate' => $this['moderate'] ?? 0,
            'high' => $this['high'] ?? 0,
            'critical' => $this['critical'] ?? 0,
        ];
    }
}
