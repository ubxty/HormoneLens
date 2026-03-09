<?php

namespace App\Services\Prediction;

use App\Models\Simulation;

class HbA1cPredictionService
{
    /**
     * Predict HbA1c trend based on historical simulation data and current snapshot.
     *
     * HbA1c represents ~3-month average blood sugar:
     * - Normal: < 5.7%
     * - Pre-diabetic: 5.7–6.4%
     * - Diabetic: ≥ 6.5%
     */
    public function predict(array $snapshot, int $userId): array
    {
        $diabetesData = $snapshot['diabetes'] ?? null;
        $hp = $snapshot['health_profile'] ?? [];

        // Get current HbA1c if available
        $currentHbA1c = $diabetesData ? (float) ($diabetesData['hba1c'] ?? 0) : 0;
        $currentBloodSugar = $diabetesData ? (float) ($diabetesData['avg_blood_sugar'] ?? 120) : 120;

        // If no diabetes data, estimate from blood sugar across all diseases
        if (!$diabetesData) {
            foreach ($snapshot as $key => $data) {
                if ($key !== 'health_profile' && is_array($data) && isset($data['avg_blood_sugar'])) {
                    $currentBloodSugar = (float) $data['avg_blood_sugar'];
                    break;
                }
            }
        }

        // Estimate HbA1c from average blood sugar if not provided
        // Formula: HbA1c = (avg_blood_sugar + 46.7) / 28.7
        if ($currentHbA1c <= 0) {
            $currentHbA1c = ($currentBloodSugar + 46.7) / 28.7;
        }

        // Get recent simulation history to detect trend
        $recentSimulations = Simulation::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'asc')
            ->get(['simulated_risk_score', 'original_risk_score', 'risk_change', 'results', 'created_at']);

        // Calculate lifestyle impact on projected HbA1c
        $lifestyleDelta = $this->calculateLifestyleDelta($hp);
        $simulationTrend = $this->calculateSimulationTrend($recentSimulations);

        // Project HbA1c change over 3 months
        $monthlyImpact = ($lifestyleDelta + $simulationTrend) * 0.1; // scale factor
        $projected3Month = max(4.0, min(14.0, $currentHbA1c + ($monthlyImpact * 3)));
        $projected6Month = max(4.0, min(14.0, $currentHbA1c + ($monthlyImpact * 6)));

        $currentCategory = $this->categorize($currentHbA1c);
        $projectedCategory = $this->categorize($projected3Month);

        return [
            'current_hba1c' => round($currentHbA1c, 1),
            'current_category' => $currentCategory,
            'current_avg_blood_sugar' => round($currentBloodSugar, 0),
            'projections' => [
                '3_month' => [
                    'estimated_hba1c' => round($projected3Month, 1),
                    'category' => $projectedCategory,
                    'trend' => $projected3Month < $currentHbA1c ? 'improving' : ($projected3Month > $currentHbA1c ? 'worsening' : 'stable'),
                    'change' => round($projected3Month - $currentHbA1c, 2),
                ],
                '6_month' => [
                    'estimated_hba1c' => round($projected6Month, 1),
                    'category' => $this->categorize($projected6Month),
                    'trend' => $projected6Month < $currentHbA1c ? 'improving' : ($projected6Month > $currentHbA1c ? 'worsening' : 'stable'),
                    'change' => round($projected6Month - $currentHbA1c, 2),
                ],
            ],
            'lifestyle_impact' => round($lifestyleDelta, 2),
            'simulation_trend' => round($simulationTrend, 2),
            'recent_simulation_count' => $recentSimulations->count(),
            'risk_factors' => $this->identifyRiskFactors($hp, $snapshot),
        ];
    }

    /**
     * Calculate lifestyle delta (positive = worsening, negative = improving).
     */
    private function calculateLifestyleDelta(array $hp): float
    {
        $delta = 0.0;

        // Sleep
        $sleep = (float) ($hp['avg_sleep_hours'] ?? 7);
        $delta += match (true) {
            $sleep < 5 => 0.3,
            $sleep < 6 => 0.15,
            $sleep >= 8 => -0.1,
            default => 0.0,
        };

        // Stress
        $stress = is_object($hp['stress_level'] ?? null) ? $hp['stress_level']->value : ($hp['stress_level'] ?? 'medium');
        $delta += match ($stress) {
            'high' => 0.2,
            'low' => -0.1,
            default => 0.0,
        };

        // Activity
        $activity = is_object($hp['physical_activity'] ?? null) ? $hp['physical_activity']->value : ($hp['physical_activity'] ?? 'moderate');
        $delta += match ($activity) {
            'sedentary' => 0.2,
            'active' => -0.15,
            'very_active' => -0.2,
            default => 0.0,
        };

        // Water intake
        if (($hp['water_intake'] ?? 2) < 2) {
            $delta += 0.05;
        }

        return $delta;
    }

    /**
     * Calculate trend from recent simulations (positive = getting worse).
     */
    private function calculateSimulationTrend($simulations): float
    {
        if ($simulations->isEmpty()) {
            return 0.0;
        }

        $avgRiskChange = $simulations->avg('risk_change');

        // Scale risk change to HbA1c impact
        return $avgRiskChange * 0.02;
    }

    private function categorize(float $hba1c): string
    {
        return match (true) {
            $hba1c < 5.7 => 'normal',
            $hba1c < 6.5 => 'pre_diabetic',
            $hba1c < 8.0 => 'diabetic_controlled',
            default => 'diabetic_uncontrolled',
        };
    }

    private function identifyRiskFactors(array $hp, array $snapshot): array
    {
        $risks = [];

        $sleep = (float) ($hp['avg_sleep_hours'] ?? 7);
        if ($sleep < 6) $risks[] = 'Insufficient sleep increases insulin resistance';

        $stress = is_object($hp['stress_level'] ?? null) ? $hp['stress_level']->value : ($hp['stress_level'] ?? 'medium');
        if ($stress === 'high') $risks[] = 'High stress elevates cortisol and blood sugar';

        $activity = is_object($hp['physical_activity'] ?? null) ? $hp['physical_activity']->value : ($hp['physical_activity'] ?? 'moderate');
        if ($activity === 'sedentary') $risks[] = 'Sedentary lifestyle reduces glucose utilization';

        foreach ($snapshot as $key => $data) {
            if ($key !== 'health_profile' && is_array($data)) {
                if (isset($data['sugar_cravings']) && $data['sugar_cravings'] === 'frequent') {
                    $risks[] = 'Frequent sugar cravings indicate poor glycemic control';
                    break;
                }
            }
        }

        return $risks;
    }
}
