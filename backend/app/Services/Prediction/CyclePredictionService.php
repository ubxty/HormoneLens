<?php

namespace App\Services\Prediction;

class CyclePredictionService
{
    /**
     * Predict ovulation stability and cycle delay risk for PCOS users.
     * Also handles period regularity prediction tied to physical activity (S7).
     */
    public function predict(array $snapshot): array
    {
        $hp = $snapshot['health_profile'] ?? [];
        $pcodData = $snapshot['pcod'] ?? $snapshot['pcos'] ?? null;

        if (!$pcodData) {
            return $this->noDataResult();
        }

        $cycleReg = $pcodData['cycle_regularity'] ?? 'regular';
        $delayRiskScore = 0.0;
        $ovulationRiskScore = 0.0;
        $factors = [];

        // Base risk from current cycle status
        $cycleBaseRisk = match ($cycleReg) {
            'missed' => 40.0,
            'irregular' => 25.0,
            'regular' => 5.0,
            default => 15.0,
        };
        $delayRiskScore += $cycleBaseRisk;
        $ovulationRiskScore += $cycleBaseRisk * 0.8;
        $factors['cycle_status'] = ['value' => $cycleReg, 'impact' => $cycleBaseRisk];

        // Stress — major disruptor of menstrual cycles
        $stress = is_object($hp['stress_level'] ?? null) ? $hp['stress_level']->value : ($hp['stress_level'] ?? 'medium');
        $stressImpact = match ($stress) {
            'high' => 20.0,
            'medium' => 8.0,
            'low' => 0.0,
            default => 5.0,
        };
        $delayRiskScore += $stressImpact;
        $ovulationRiskScore += $stressImpact * 1.2; // stress affects ovulation even more
        $factors['stress'] = ['value' => $stress, 'impact' => $stressImpact];

        // Sleep — circadian rhythm affects reproductive hormones
        $sleepHours = (float) ($hp['avg_sleep_hours'] ?? 7);
        $sleepImpact = match (true) {
            $sleepHours < 5 => 15.0,
            $sleepHours < 6 => 10.0,
            $sleepHours < 7 => 3.0,
            default => 0.0,
        };
        $delayRiskScore += $sleepImpact;
        $ovulationRiskScore += $sleepImpact;
        $factors['sleep'] = ['value' => $sleepHours, 'impact' => $sleepImpact];

        // Physical activity — both extremes are harmful
        $activity = is_object($hp['physical_activity'] ?? null) ? $hp['physical_activity']->value : ($hp['physical_activity'] ?? 'moderate');
        $activityImpact = match ($activity) {
            'sedentary' => 12.0,
            'moderate' => -5.0,  // beneficial
            'active' => -3.0,
            'very_active' => 10.0, // overtraining disrupts cycles
            default => 0.0,
        };
        $delayRiskScore += $activityImpact;
        $ovulationRiskScore += $activityImpact;
        $factors['physical_activity'] = ['value' => $activity, 'impact' => $activityImpact];

        // BMI — both underweight and overweight affect cycles
        $weight = (float) ($hp['weight'] ?? 70);
        $height = (float) ($hp['height'] ?? 165);
        $heightM = $height / 100;
        $bmi = $heightM > 0 ? $weight / ($heightM * $heightM) : 25;

        $bmiImpact = match (true) {
            $bmi < 18.5 => 15.0,  // underweight
            $bmi > 30 => 12.0,     // obese
            $bmi >= 25 => 6.0,     // overweight
            default => 0.0,
        };
        $delayRiskScore += $bmiImpact;
        $ovulationRiskScore += $bmiImpact;
        $factors['bmi'] = ['value' => round($bmi, 1), 'impact' => $bmiImpact];

        // Blood sugar / insulin resistance
        $bloodSugar = null;
        foreach ($snapshot as $key => $data) {
            if ($key !== 'health_profile' && is_array($data) && isset($data['avg_blood_sugar'])) {
                $bloodSugar = (float) $data['avg_blood_sugar'];
                break;
            }
        }
        $sugarImpact = match (true) {
            $bloodSugar !== null && $bloodSugar > 200 => 10.0,
            $bloodSugar !== null && $bloodSugar > 140 => 5.0,
            default => 0.0,
        };
        $delayRiskScore += $sugarImpact;
        $ovulationRiskScore += $sugarImpact;
        $factors['blood_sugar'] = ['value' => $bloodSugar, 'impact' => $sugarImpact];

        // Clamp scores
        $delayRiskScore = max(0, min(100, $delayRiskScore));
        $ovulationRiskScore = max(0, min(100, $ovulationRiskScore));

        // Predicted cycle length variation
        $normalCycleLength = 28;
        $predictedDelay = (int) round(($delayRiskScore / 100) * 14); // up to 14 days delay at max risk
        $predictedCycleLength = $normalCycleLength + $predictedDelay;

        // Period regularity prediction (S7)
        $regularityPrediction = $this->predictRegularity($delayRiskScore, $cycleReg, $factors);

        return [
            'cycle_delay_risk' => [
                'score' => round($delayRiskScore, 1),
                'category' => $this->categorize($delayRiskScore),
                'predicted_delay_days' => $predictedDelay,
                'predicted_cycle_length' => $predictedCycleLength,
            ],
            'ovulation_stability' => [
                'score' => round($ovulationRiskScore, 1),
                'category' => $this->categorize($ovulationRiskScore),
                'status' => $ovulationRiskScore > 60 ? 'unstable' : ($ovulationRiskScore > 35 ? 'variable' : 'stable'),
            ],
            'period_regularity' => $regularityPrediction,
            'factors' => $factors,
        ];
    }

    /**
     * Predict period regularity based on lifestyle factors.
     */
    private function predictRegularity(float $delayRisk, string $currentRegularity, array $factors): array
    {
        // Determine if current lifestyle is improving or worsening regularity
        $positiveFactors = 0;
        $negativeFactors = 0;

        foreach ($factors as $factor) {
            $impact = $factor['impact'] ?? 0;
            if ($impact < 0) $positiveFactors++;
            if ($impact > 10) $negativeFactors++;
        }

        $prediction = match (true) {
            $delayRisk < 25 => 'likely_regular',
            $delayRisk < 50 => 'may_be_irregular',
            $delayRisk < 75 => 'likely_irregular',
            default => 'high_risk_of_missed_periods',
        };

        $trend = $positiveFactors > $negativeFactors ? 'improving' : ($negativeFactors > $positiveFactors ? 'worsening' : 'stable');

        return [
            'current' => $currentRegularity,
            'prediction' => $prediction,
            'trend' => $trend,
            'improvement_possible' => $negativeFactors > 0,
        ];
    }

    private function categorize(float $score): string
    {
        return match (true) {
            $score >= 70 => 'high',
            $score >= 40 => 'moderate',
            default => 'low',
        };
    }

    private function noDataResult(): array
    {
        return [
            'cycle_delay_risk' => ['score' => 0, 'category' => 'not_applicable', 'predicted_delay_days' => 0, 'predicted_cycle_length' => 28],
            'ovulation_stability' => ['score' => 0, 'category' => 'not_applicable', 'status' => 'not_applicable'],
            'period_regularity' => ['current' => 'unknown', 'prediction' => 'not_applicable', 'trend' => 'unknown', 'improvement_possible' => false],
            'factors' => [],
        ];
    }
}
