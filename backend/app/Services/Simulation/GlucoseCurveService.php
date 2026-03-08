<?php

namespace App\Services\Simulation;

use App\Models\FoodGlycemicData;

class GlucoseCurveService
{
    /**
     * Generate a time-dependent glucose response curve.
     *
     * @param  string  $foodItem       Name of food
     * @param  array   $snapshot       Digital twin snapshot_data
     * @param  string|null  $mealTime  Optional: 'morning','afternoon','evening','night'
     * @return array{curve: array, peak: array, food: array, modifiers: array}
     */
    public function predict(string $foodItem, array $snapshot, ?string $mealTime = null): array
    {
        $food = FoodGlycemicData::findByName($foodItem);

        // Fallback estimation for unknown foods
        if (!$food) {
            $food = $this->estimateGlycemicData($foodItem);
        }

        $hp = $snapshot['health_profile'] ?? [];
        $baseline = $this->extractBaseline($snapshot);
        $modifiers = $this->computeModifiers($hp, $mealTime);

        // Adjusted spike considering all cross-factors
        $adjustedSpike = $food->typical_spike_mg_dl * $modifiers['combined'];
        $adjustedPeakTime = (int) round($food->peak_time_minutes * $modifiers['peak_time_factor']);
        $adjustedRecovery = (int) round($food->recovery_time_minutes * $modifiers['recovery_factor']);

        $curve = $this->buildCurve($baseline, $adjustedSpike, $adjustedPeakTime, $adjustedRecovery);
        $peakGlucose = $baseline + $adjustedSpike;

        return [
            'curve' => $curve,
            'peak' => [
                'glucose_mg_dl' => round($peakGlucose, 1),
                'time_minutes' => $adjustedPeakTime,
            ],
            'recovery_minutes' => $adjustedRecovery,
            'baseline_mg_dl' => $baseline,
            'food' => [
                'name' => $food instanceof FoodGlycemicData ? $food->food_item : $food->food_item,
                'glycemic_index' => $food->glycemic_index,
                'glycemic_load' => $food->glycemic_load,
                'category' => $food->category,
                'serving_size' => $food->serving_size,
                'alternatives' => $food instanceof FoodGlycemicData ? ($food->alternatives ?? []) : ($food->alternatives ?? []),
                'from_database' => $food instanceof FoodGlycemicData,
            ],
            'modifiers' => $modifiers,
        ];
    }

    /**
     * Build the glucose curve as time-series data points.
     * Uses a skewed Gaussian model: fast rise to peak, slower recovery.
     */
    private function buildCurve(float $baseline, float $spike, int $peakMin, int $recoveryMin): array
    {
        $totalDuration = $peakMin + $recoveryMin;
        $points = [];
        $interval = max(5, (int) round($totalDuration / 30)); // ~30 data points

        for ($t = 0; $t <= $totalDuration; $t += $interval) {
            $glucose = $this->glucoseAtTime($t, $baseline, $spike, $peakMin, $recoveryMin);
            $points[] = [
                'time_minutes' => $t,
                'glucose_mg_dl' => round($glucose, 1),
            ];
        }

        // Ensure endpoint is included
        $lastT = end($points)['time_minutes'];
        if ($lastT < $totalDuration) {
            $points[] = [
                'time_minutes' => $totalDuration,
                'glucose_mg_dl' => round($baseline + ($spike * 0.05), 1), // nearly back to baseline
            ];
        }

        return $points;
    }

    /**
     * Calculate glucose at a given time using asymmetric Gaussian.
     * Rise phase: quadratic rise to peak.
     * Fall phase: exponential decay back toward baseline.
     */
    private function glucoseAtTime(int $t, float $baseline, float $spike, int $peakMin, int $recoveryMin): float
    {
        if ($t <= 0) {
            return $baseline;
        }

        if ($t <= $peakMin) {
            // Rise phase: quadratic
            $progress = $t / $peakMin;
            $rise = $spike * (1 - pow(1 - $progress, 2));
            return $baseline + $rise;
        }

        // Decay phase: exponential
        $decayProgress = ($t - $peakMin) / $recoveryMin;
        $remaining = $spike * exp(-3 * $decayProgress);
        return $baseline + max(0, $remaining);
    }

    /**
     * Compute cross-factor modifiers from the user's health state.
     */
    private function computeModifiers(array $hp, ?string $mealTime): array
    {
        // Sleep modifier: poor sleep worsens insulin sensitivity
        $sleepHours = (float) ($hp['avg_sleep_hours'] ?? 7);
        $sleepMod = match (true) {
            $sleepHours < 5 => 1.40,
            $sleepHours < 6 => 1.30,
            $sleepHours < 7 => 1.10,
            $sleepHours >= 8 => 0.95,
            default => 1.0,
        };

        // Stress modifier
        $stress = is_object($hp['stress_level'] ?? null) ? $hp['stress_level']->value : ($hp['stress_level'] ?? 'medium');
        $stressMod = match ($stress) {
            'high' => 1.20,
            'medium' => 1.05,
            'low' => 0.90,
            default => 1.0,
        };

        // Physical activity modifier
        $activity = is_object($hp['physical_activity'] ?? null) ? $hp['physical_activity']->value : ($hp['physical_activity'] ?? 'moderate');
        $activityMod = match ($activity) {
            'sedentary' => 1.15,
            'moderate' => 1.0,
            'active' => 0.85,
            'very_active' => 0.75,
            default => 1.0,
        };

        // Meal timing modifier (circadian insulin sensitivity)
        $mealTimeMod = match ($mealTime) {
            'morning' => 0.85,    // 6–10am: best insulin sensitivity
            'afternoon' => 1.0,   // 12–3pm: moderate
            'evening' => 1.15,    // 6–9pm: reduced sensitivity
            'night' => 1.35,      // 10pm–2am: worst
            default => 1.0,       // not specified
        };

        $combined = $sleepMod * $stressMod * $activityMod * $mealTimeMod;

        // Peak time factor: how quickly glucose peaks (active = faster peak)
        $peakTimeFactor = match ($activity) {
            'sedentary' => 1.12,
            'moderate' => 1.0,
            'active'    => 0.90,
            'very_active' => 0.82,
            default => 1.0,
        };
        if ($stress === 'high') {
            $peakTimeFactor *= 0.95; // high stress → slightly faster, more erratic peak
        }

        // Recovery factor: how long it takes to return to baseline (poor metabolic health = slower)
        $recoveryFactor = round($sleepMod * $stressMod, 3);

        return [
            'sleep' => ['value' => $sleepHours, 'factor' => $sleepMod, 'label' => $sleepHours < 7 ? 'Poor sleep increases spike' : 'Good sleep helps'],
            'stress' => ['value' => $stress, 'factor' => $stressMod, 'label' => $stress === 'high' ? 'High stress amplifies spike' : ($stress === 'low' ? 'Low stress reduces spike' : 'Moderate stress effect')],
            'activity' => ['value' => $activity, 'factor' => $activityMod, 'label' => $activity === 'active' ? 'Active lifestyle reduces spike' : ($activity === 'sedentary' ? 'Sedentary lifestyle worsens spike' : 'Moderate activity effect')],
            'meal_time' => ['value' => $mealTime ?? 'unspecified', 'factor' => $mealTimeMod, 'label' => $this->mealTimeLabel($mealTime)],
            'combined' => round($combined, 3),
            'peak_time_factor' => round($peakTimeFactor, 3),
            'recovery_factor' => $recoveryFactor,
        ];
    }

    private function mealTimeLabel(?string $mealTime): string
    {
        return match ($mealTime) {
            'morning' => 'Morning = best insulin sensitivity',
            'afternoon' => 'Afternoon = moderate response',
            'evening' => 'Evening = reduced insulin sensitivity',
            'night' => 'Late night = worst glucose response',
            default => 'Timing not specified',
        };
    }

    /**
     * Extract baseline blood sugar from twin snapshot.
     */
    private function extractBaseline(array $snapshot): float
    {
        foreach ($snapshot as $key => $data) {
            if ($key === 'health_profile' || !is_array($data)) {
                continue;
            }
            if (isset($data['avg_blood_sugar'])) {
                return (float) $data['avg_blood_sugar'];
            }
        }
        return 100.0; // default fasting glucose
    }

    /**
     * Estimate glycemic data for unknown foods using conservative defaults.
     */
    private function estimateGlycemicData(string $foodItem): object
    {
        return (object) [
            'food_item' => $foodItem,
            'category' => 'unknown',
            'glycemic_index' => 55,
            'glycemic_load' => 15,
            'typical_spike_mg_dl' => 30,
            'peak_time_minutes' => 45,
            'recovery_time_minutes' => 90,
            'serving_size' => '1 serving',
            'alternatives' => [],
        ];
    }
}
