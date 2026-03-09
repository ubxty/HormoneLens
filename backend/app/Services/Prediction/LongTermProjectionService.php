<?php

namespace App\Services\Prediction;

use App\Models\Simulation;

class LongTermProjectionService
{
    public function __construct(
        private readonly CortisolPredictionService $cortisol,
        private readonly AndrogenPredictionService $androgen,
        private readonly CyclePredictionService $cycle,
        private readonly HbA1cPredictionService $hba1c,
    ) {}

    /**
     * Generate long-term outcome predictions (S6).
     * Covers: PCOS severity progression, diabetes complication risk, fertility health risk.
     */
    public function project(array $snapshot, int $userId): array
    {
        $hp = $snapshot['health_profile'] ?? [];
        $diseaseType = $hp['disease_type'] ?? null;

        $results = [
            'overall_trajectory' => $this->calculateTrajectory($snapshot, $userId),
        ];

        // PCOS-specific projections
        $hasPcod = isset($snapshot['pcod']) || isset($snapshot['pcos']);
        if ($hasPcod) {
            $results['pcos_progression'] = $this->projectPcosProgression($snapshot);
            $results['fertility_risk'] = $this->projectFertilityRisk($snapshot);
        }

        // Diabetes-specific projections
        if (isset($snapshot['diabetes'])) {
            $results['diabetes_complications'] = $this->projectDiabetesComplications($snapshot, $userId);
        }

        // Thyroid-specific projections
        if (isset($snapshot['thyroid'])) {
            $results['thyroid_progression'] = $this->projectThyroidProgression($snapshot);
        }

        return $results;
    }

    /**
     * Calculate overall health trajectory from simulation history.
     */
    private function calculateTrajectory(array $snapshot, int $userId): array
    {
        $simulations = Simulation::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'asc')
            ->get(['simulated_risk_score', 'original_risk_score', 'risk_change', 'created_at']);

        if ($simulations->isEmpty()) {
            return [
                'trend' => 'insufficient_data',
                'data_points' => 0,
                'message' => 'Run more simulations to see your health trajectory.',
            ];
        }

        $avgRiskChange = $simulations->avg('risk_change');
        $latestRisk = $simulations->last()->simulated_risk_score;
        $firstRisk = $simulations->first()->original_risk_score;
        $overallChange = (float) $latestRisk - (float) $firstRisk;

        return [
            'trend' => $overallChange < -2 ? 'improving' : ($overallChange > 2 ? 'worsening' : 'stable'),
            'avg_risk_change_per_sim' => round($avgRiskChange, 2),
            'overall_risk_change' => round($overallChange, 2),
            'data_points' => $simulations->count(),
            'latest_risk_score' => round((float) $latestRisk, 1),
        ];
    }

    /**
     * Project PCOS severity progression over time.
     */
    private function projectPcosProgression(array $snapshot): array
    {
        $androgenResult = $this->androgen->predict($snapshot);
        $cycleResult = $this->cycle->predict($snapshot);

        $severityScore = ($androgenResult['androgen_imbalance_risk_score'] * 0.4)
            + ($cycleResult['cycle_delay_risk']['score'] * 0.3)
            + ($cycleResult['ovulation_stability']['score'] * 0.3);

        $currentSeverity = match (true) {
            $severityScore >= 70 => 'severe',
            $severityScore >= 45 => 'moderate',
            $severityScore >= 20 => 'mild',
            default => 'minimal',
        };

        // Project based on lifestyle factors
        $hp = $snapshot['health_profile'] ?? [];
        $lifestyleScore = $this->scoreLifestyleQuality($hp);

        $projectedTrend = match (true) {
            $lifestyleScore >= 70 => 'likely_improving',
            $lifestyleScore >= 40 => 'likely_stable',
            default => 'may_worsen',
        };

        return [
            'current_severity' => $currentSeverity,
            'severity_score' => round($severityScore, 1),
            'projected_trend' => $projectedTrend,
            'androgen_risk' => $androgenResult['risk_category'],
            'cycle_risk' => $cycleResult['cycle_delay_risk']['category'],
            'key_concern' => $severityScore >= 50
                ? 'PCOS markers suggest active hormonal imbalance — lifestyle changes recommended.'
                : 'PCOS markers are within manageable range with current lifestyle.',
        ];
    }

    /**
     * Project fertility health risk for PCOS users.
     */
    private function projectFertilityRisk(array $snapshot): array
    {
        $cycleResult = $this->cycle->predict($snapshot);
        $ovulationScore = $cycleResult['ovulation_stability']['score'];

        $fertilityRisk = match (true) {
            $ovulationScore >= 70 => 'high',
            $ovulationScore >= 40 => 'moderate',
            default => 'low',
        };

        return [
            'risk_level' => $fertilityRisk,
            'ovulation_stability' => $cycleResult['ovulation_stability']['status'],
            'cycle_regularity_prediction' => $cycleResult['period_regularity']['prediction'],
            'recommendation' => $fertilityRisk === 'high'
                ? 'Ovulation appears unstable. Consider consulting a fertility specialist alongside lifestyle improvements.'
                : 'Maintain current healthy habits to support reproductive health.',
        ];
    }

    /**
     * Project diabetes complication risk.
     */
    private function projectDiabetesComplications(array $snapshot, int $userId): array
    {
        $hba1cResult = $this->hba1c->predict($snapshot, $userId);
        $projectedHbA1c = $hba1cResult['projections']['3_month']['estimated_hba1c'];
        $cortisolResult = $this->cortisol->predict($snapshot);

        $complicationRisk = 0;

        // HbA1c-based risk
        $complicationRisk += match (true) {
            $projectedHbA1c >= 9.0 => 35,
            $projectedHbA1c >= 8.0 => 25,
            $projectedHbA1c >= 7.0 => 15,
            $projectedHbA1c >= 6.5 => 8,
            default => 0,
        };

        // Blood sugar variability
        $bloodSugar = $snapshot['diabetes']['avg_blood_sugar'] ?? 120;
        $complicationRisk += match (true) {
            $bloodSugar > 250 => 25,
            $bloodSugar > 200 => 15,
            $bloodSugar > 160 => 8,
            default => 0,
        };

        // Cortisol-driven insulin resistance
        if ($cortisolResult['status'] === 'elevated') {
            $complicationRisk += 10;
        }

        // Lifestyle factors
        $hp = $snapshot['health_profile'] ?? [];
        $lifestyleScore = $this->scoreLifestyleQuality($hp);
        $complicationRisk += max(0, (50 - $lifestyleScore) * 0.3);

        $complicationRisk = min(100, $complicationRisk);

        return [
            'complication_risk_score' => round($complicationRisk, 1),
            'risk_level' => match (true) {
                $complicationRisk >= 60 => 'high',
                $complicationRisk >= 35 => 'moderate',
                default => 'low',
            },
            'hba1c_trend' => $hba1cResult['projections']['3_month']['trend'],
            'projected_hba1c_3mo' => $hba1cResult['projections']['3_month']['estimated_hba1c'],
            'cortisol_status' => $cortisolResult['status'],
            'primary_risks' => $this->identifyDiabetesRisks($complicationRisk, $hba1cResult, $cortisolResult),
        ];
    }

    /**
     * Project thyroid condition progression.
     */
    private function projectThyroidProgression(array $snapshot): array
    {
        $thyroidData = $snapshot['thyroid'] ?? [];
        $hp = $snapshot['health_profile'] ?? [];

        $tshLevel = (float) ($thyroidData['tsh_level'] ?? 3.0);
        $cortisolResult = $this->cortisol->predict($snapshot);

        // TSH instability risk
        $instabilityRisk = 0;

        // Current TSH level
        $instabilityRisk += match (true) {
            $tshLevel > 10 || $tshLevel < 0.4 => 30,
            $tshLevel > 5 || $tshLevel < 0.5 => 20,
            $tshLevel > 4 || $tshLevel < 1.0 => 10,
            default => 0,
        };

        // Stress impact on thyroid
        $stress = is_object($hp['stress_level'] ?? null) ? $hp['stress_level']->value : ($hp['stress_level'] ?? 'medium');
        $instabilityRisk += match ($stress) {
            'high' => 15,
            'medium' => 5,
            default => 0,
        };

        // Sleep impact
        $sleep = (float) ($hp['avg_sleep_hours'] ?? 7);
        if ($sleep < 6) $instabilityRisk += 10;

        // Weight change propensity
        $weight = (float) ($hp['weight'] ?? 70);
        $height = (float) ($hp['height'] ?? 165);
        $heightM = $height / 100;
        $bmi = $heightM > 0 ? $weight / ($heightM * $heightM) : 25;

        $weightGainPropensity = match (true) {
            $tshLevel > 5 && $bmi > 25 => 'high',
            $tshLevel > 4 || $bmi > 25 => 'moderate',
            default => 'low',
        };

        // Metabolic rate impact
        $metabolicImpact = match (true) {
            $tshLevel > 5 => 'slowed',
            $tshLevel < 1.0 => 'elevated',
            default => 'normal',
        };

        // Fatigue risk
        $fatigueRisk = $instabilityRisk > 30 ? 'high' : ($instabilityRisk > 15 ? 'moderate' : 'low');

        return [
            'tsh_instability_risk' => round(min(100, $instabilityRisk), 1),
            'current_tsh' => $tshLevel,
            'metabolic_rate_impact' => $metabolicImpact,
            'weight_gain_propensity' => $weightGainPropensity,
            'fatigue_risk' => $fatigueRisk,
            'condition_trend' => $tshLevel > 5 ? 'hypothyroid_tendency' : ($tshLevel < 0.5 ? 'hyperthyroid_tendency' : 'euthyroid'),
            'cortisol_interaction' => $cortisolResult['status'],
        ];
    }

    /**
     * Score lifestyle quality (0-100, higher = better).
     */
    private function scoreLifestyleQuality(array $hp): float
    {
        $score = 50.0;

        $sleep = (float) ($hp['avg_sleep_hours'] ?? 7);
        $score += match (true) {
            $sleep >= 7 && $sleep <= 9 => 15,
            $sleep >= 6 => 5,
            default => -10,
        };

        $stress = is_object($hp['stress_level'] ?? null) ? $hp['stress_level']->value : ($hp['stress_level'] ?? 'medium');
        $score += match ($stress) {
            'low' => 15,
            'medium' => 0,
            'high' => -15,
            default => 0,
        };

        $activity = is_object($hp['physical_activity'] ?? null) ? $hp['physical_activity']->value : ($hp['physical_activity'] ?? 'moderate');
        $score += match ($activity) {
            'active', 'very_active' => 15,
            'moderate' => 5,
            'sedentary' => -10,
            default => 0,
        };

        if (($hp['water_intake'] ?? 2) >= 2) $score += 5;

        return max(0, min(100, $score));
    }

    private function identifyDiabetesRisks(float $riskScore, array $hba1c, array $cortisol): array
    {
        $risks = [];
        if ($hba1c['projections']['3_month']['trend'] === 'worsening') {
            $risks[] = 'HbA1c projected to increase — indicating worsening glycemic control.';
        }
        if ($cortisol['status'] === 'elevated') {
            $risks[] = 'Elevated cortisol can worsen insulin resistance.';
        }
        if ($riskScore >= 50) {
            $risks[] = 'Current lifestyle pattern suggests increased risk of long-term complications.';
        }
        return $risks;
    }
}
