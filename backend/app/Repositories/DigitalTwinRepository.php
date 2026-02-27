<?php

namespace App\Repositories;

use App\Models\DigitalTwin;
use App\Models\User;

class DigitalTwinRepository
{
    public function findActiveByUser(User $user): ?DigitalTwin
    {
        return DigitalTwin::where('user_id', $user->id)
            ->where('is_active', true)
            ->latest()
            ->first();
    }

    public function findById(int $id): ?DigitalTwin
    {
        return DigitalTwin::find($id);
    }

    public function create(array $data): DigitalTwin
    {
        return DigitalTwin::create($data);
    }

    public function deactivateAll(User $user): void
    {
        DigitalTwin::where('user_id', $user->id)->update(['is_active' => false]);
    }

    public function getByUser(User $user)
    {
        return DigitalTwin::where('user_id', $user->id)->latest()->get();
    }

    public function riskDistribution()
    {
        return DigitalTwin::where('is_active', true)
            ->selectRaw('risk_category, COUNT(*) as count')
            ->groupBy('risk_category')
            ->pluck('count', 'risk_category');
    }

    public function averageRiskScore(): float
    {
        return (float) DigitalTwin::where('is_active', true)->avg('overall_risk_score') ?? 0;
    }

    public function dailyRiskScoresForPeriod(int $days = 30): array
    {
        return DigitalTwin::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, ROUND(AVG(overall_risk_score), 2) as avg_score')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => $row->date, 'avg_score' => (float) $row->avg_score])
            ->values()
            ->toArray();
    }
}
