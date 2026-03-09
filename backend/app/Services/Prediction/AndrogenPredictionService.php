<?php

namespace App\Services\Prediction;

class AndrogenPredictionService
{
    /**
     * Predict androgen imbalance risk for PCOS users.
     * Returns risk assessment based on lifestyle and disease factors.
     *
     * Higher androgen levels in women indicate conditions like PCOS.
     * Normal total testosterone in women: 15–70 ng/dL
     */
    public function predict(array $snapshot): array
    {
        $hp = $snapshot['health_profile'] ?? [];
        $pcodData = $snapshot['pcod'] ?? $snapshot['pcos'] ?? null;

        if (!$pcodData) {
            return $this->noDataResult();
        }

        $riskScore = 0.0;
        $factors = [];

        // BMI — obesity strongly linked to hyperandrogenism
        $weight = (float) ($hp['weight'] ?? 70);
        $height = (float) ($hp['height'] ?? 165);
        $heightM = $height / 100;
        $bmi = $heightM > 0 ? $weight / ($heightM * $heightM) : 25;

        $bmiImpact = match (true) {
            $bmi > 35 => 25.0,
            $bmi > 30 => 18.0,
            $bmi >= 25 => 10.0,
            default => 0.0,
        };
        $riskScore += $bmiImpact;
        $factors['bmi'] = ['value' => round($bmi, 1), 'impact' => $bmiImpact];

        // Insulin resistance worsens androgen production
        $bloodSugar = null;
        foreach ($snapshot as $key => $data) {
            if ($key !== 'health_profile' && is_array($data) && isset($data['avg_blood_sugar'])) {
                $bloodSugar = (float) $data['avg_blood_sugar'];
                break;
            }
        }
        $insulinImpact = match (true) {
            $bloodSugar !== null && $bloodSugar > 200 => 20.0,
            $bloodSugar !== null && $bloodSugar > 140 => 12.0,
            $bloodSugar !== null && $bloodSugar > 110 => 5.0,
            default => 0.0,
        };
        $riskScore += $insulinImpact;
        $factors['insulin_resistance'] = ['value' => $bloodSugar, 'impact' => $insulinImpact];

        // Cycle irregularity — strong marker of hyperandrogenism
        $cycleReg = $pcodData['cycle_regularity'] ?? null;
        $cycleImpact = match ($cycleReg) {
            'missed' => 20.0,
            'irregular' => 15.0,
            'regular' => 0.0,
            default => 5.0,
        };
        $riskScore += $cycleImpact;
        $factors['cycle_regularity'] = ['value' => $cycleReg, 'impact' => $cycleImpact];

        // Stress — increases adrenal androgen production
        $stress = is_object($hp['stress_level'] ?? null) ? $hp['stress_level']->value : ($hp['stress_level'] ?? 'medium');
        $stressImpact = match ($stress) {
            'high' => 12.0,
            'medium' => 5.0,
            'low' => 0.0,
            default => 3.0,
        };
        $riskScore += $stressImpact;
        $factors['stress'] = ['value' => $stress, 'impact' => $stressImpact];

        // Sleep — poor sleep disrupts hormone regulation
        $sleepHours = (float) ($hp['avg_sleep_hours'] ?? 7);
        $sleepImpact = match (true) {
            $sleepHours < 5 => 10.0,
            $sleepHours < 6 => 6.0,
            $sleepHours < 7 => 2.0,
            default => 0.0,
        };
        $riskScore += $sleepImpact;
        $factors['sleep'] = ['value' => $sleepHours, 'impact' => $sleepImpact];

        // Sugar cravings — marker of insulin-driven androgenism
        $cravings = $pcodData['sugar_cravings'] ?? null;
        $cravingImpact = match ($cravings) {
            'frequent' => 8.0,
            'occasional' => 3.0,
            default => 0.0,
        };
        $riskScore += $cravingImpact;
        $factors['sugar_cravings'] = ['value' => $cravings, 'impact' => $cravingImpact];

        // Physical activity — exercise helps reduce androgens
        $activity = is_object($hp['physical_activity'] ?? null) ? $hp['physical_activity']->value : ($hp['physical_activity'] ?? 'moderate');
        $activityImpact = match ($activity) {
            'sedentary' => 8.0,
            'moderate' => 0.0,
            'active' => -5.0,
            'very_active' => -3.0, // overtraining can raise androgens
            default => 0.0,
        };
        $riskScore += $activityImpact;
        $factors['activity'] = ['value' => $activity, 'impact' => $activityImpact];

        $riskScore = max(0, min(100, $riskScore));
        $riskCategory = match (true) {
            $riskScore >= 70 => 'high',
            $riskScore >= 40 => 'moderate',
            default => 'low',
        };

        return [
            'androgen_imbalance_risk_score' => round($riskScore, 1),
            'risk_category' => $riskCategory,
            'estimated_testosterone_status' => $riskScore >= 50 ? 'likely_elevated' : ($riskScore >= 30 ? 'borderline' : 'likely_normal'),
            'factors' => $factors,
            'recommendations' => $this->getRecommendations($factors, $riskCategory),
        ];
    }

    private function noDataResult(): array
    {
        return [
            'androgen_imbalance_risk_score' => 0,
            'risk_category' => 'not_applicable',
            'estimated_testosterone_status' => 'not_applicable',
            'factors' => [],
            'recommendations' => [],
        ];
    }

    private function getRecommendations(array $factors, string $riskCategory): array
    {
        $recs = [];

        if ($riskCategory === 'high' || $riskCategory === 'moderate') {
            if (($factors['bmi']['impact'] ?? 0) > 10) {
                $recs[] = 'Weight management can significantly reduce androgen levels — even 5-10% weight loss helps.';
            }
            if (($factors['insulin_resistance']['impact'] ?? 0) > 5) {
                $recs[] = 'Managing blood sugar through low-GI foods can reduce insulin-driven androgen production.';
            }
            if (($factors['stress']['impact'] ?? 0) > 5) {
                $recs[] = 'Stress reduction (yoga, meditation) can lower adrenal androgen production.';
            }
            if (($factors['activity']['impact'] ?? 0) > 0) {
                $recs[] = 'Regular moderate exercise (30 min/day) helps balance hormone levels.';
            }
        }

        return $recs;
    }
}
