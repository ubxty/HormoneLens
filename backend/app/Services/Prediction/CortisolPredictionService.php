<?php

namespace App\Services\Prediction;

class CortisolPredictionService
{
    /**
     * Predict cortisol level from lifestyle factors.
     * Returns estimated cortisol in μg/dL and risk assessment.
     *
     * Normal morning cortisol: 6–23 μg/dL
     * Normal evening cortisol: 2–11 μg/dL
     */
    public function predict(array $snapshot, ?string $timeOfDay = null): array
    {
        $hp = $snapshot['health_profile'] ?? [];
        $baseCortisol = 12.0; // mid-range normal

        // Stress impact (strongest factor)
        $stress = is_object($hp['stress_level'] ?? null) ? $hp['stress_level']->value : ($hp['stress_level'] ?? 'medium');
        $stressImpact = match ($stress) {
            'high' => 8.0,
            'medium' => 3.0,
            'low' => -2.0,
            default => 0.0,
        };

        // Sleep impact — poor sleep raises cortisol
        $sleepHours = (float) ($hp['avg_sleep_hours'] ?? 7);
        $sleepImpact = match (true) {
            $sleepHours < 5 => 6.0,
            $sleepHours < 6 => 4.0,
            $sleepHours < 7 => 1.5,
            $sleepHours >= 8 => -1.0,
            default => 0.0,
        };

        // Physical activity — moderate exercise lowers cortisol, overtraining raises it
        $activity = is_object($hp['physical_activity'] ?? null) ? $hp['physical_activity']->value : ($hp['physical_activity'] ?? 'moderate');
        $activityImpact = match ($activity) {
            'sedentary' => 2.0,
            'moderate' => -1.5,
            'active' => -2.0,
            'very_active' => 1.0, // overtraining can raise cortisol
            default => 0.0,
        };

        // BMI impact
        $weight = (float) ($hp['weight'] ?? 70);
        $height = (float) ($hp['height'] ?? 165);
        $heightM = $height / 100;
        $bmi = $heightM > 0 ? $weight / ($heightM * $heightM) : 25;
        $bmiImpact = $bmi > 30 ? 2.5 : ($bmi > 25 ? 1.0 : 0.0);

        // Diurnal rhythm — cortisol peaks in morning, drops at night
        $diurnalFactor = match ($timeOfDay) {
            'morning' => 1.6,
            'afternoon' => 1.0,
            'evening' => 0.6,
            'night' => 0.4,
            default => 1.0,
        };

        // Disease-specific factors
        $diseaseImpact = 0.0;
        foreach ($snapshot as $key => $data) {
            if ($key === 'health_profile' || !is_array($data)) continue;

            // PCOS — often elevated cortisol
            if ($key === 'pcod' || $key === 'pcos') {
                $diseaseImpact += 2.0;
                if (($data['cycle_regularity'] ?? '') === 'irregular') {
                    $diseaseImpact += 1.5;
                }
            }
            // Diabetes — insulin resistance can dysregulate cortisol
            if ($key === 'diabetes') {
                $bloodSugar = (float) ($data['avg_blood_sugar'] ?? 120);
                if ($bloodSugar > 200) {
                    $diseaseImpact += 2.5;
                } elseif ($bloodSugar > 140) {
                    $diseaseImpact += 1.0;
                }
            }
            // Thyroid — hypothyroidism can alter cortisol metabolism
            if ($key === 'thyroid') {
                $diseaseImpact += 1.5;
            }
        }

        $rawCortisol = $baseCortisol + $stressImpact + $sleepImpact + $activityImpact + $bmiImpact + $diseaseImpact;
        $predictedCortisol = max(1.0, min(40.0, $rawCortisol * $diurnalFactor));

        // Risk assessment
        $normalRange = match ($timeOfDay) {
            'morning' => ['min' => 6.0, 'max' => 23.0],
            'evening', 'night' => ['min' => 2.0, 'max' => 11.0],
            default => ['min' => 4.0, 'max' => 20.0],
        };

        $isElevated = $predictedCortisol > $normalRange['max'];
        $isLow = $predictedCortisol < $normalRange['min'];
        $imbalanceRisk = $this->calculateImbalanceRisk($predictedCortisol, $normalRange);

        return [
            'predicted_cortisol_ug_dl' => round($predictedCortisol, 1),
            'normal_range' => $normalRange,
            'time_of_day' => $timeOfDay ?? 'unspecified',
            'status' => $isElevated ? 'elevated' : ($isLow ? 'low' : 'normal'),
            'imbalance_risk' => $imbalanceRisk,
            'factors' => [
                'stress' => ['impact' => round($stressImpact, 1), 'value' => $stress],
                'sleep' => ['impact' => round($sleepImpact, 1), 'value' => $sleepHours],
                'activity' => ['impact' => round($activityImpact, 1), 'value' => $activity],
                'bmi' => ['impact' => round($bmiImpact, 1), 'value' => round($bmi, 1)],
                'disease' => ['impact' => round($diseaseImpact, 1)],
            ],
        ];
    }

    /**
     * Generate a 24-hour cortisol rhythm curve.
     */
    public function dailyCurve(array $snapshot): array
    {
        $times = ['morning', 'afternoon', 'evening', 'night'];
        $curve = [];

        foreach ($times as $time) {
            $prediction = $this->predict($snapshot, $time);
            $curve[] = [
                'time_of_day' => $time,
                'cortisol_ug_dl' => $prediction['predicted_cortisol_ug_dl'],
                'status' => $prediction['status'],
            ];
        }

        return $curve;
    }

    private function calculateImbalanceRisk(float $cortisol, array $normalRange): string
    {
        $midpoint = ($normalRange['min'] + $normalRange['max']) / 2;
        $deviation = abs($cortisol - $midpoint) / $midpoint;

        return match (true) {
            $deviation > 0.8 => 'high',
            $deviation > 0.4 => 'moderate',
            default => 'low',
        };
    }
}
