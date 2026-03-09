<?php

namespace App\Services\Simulation;

use App\Models\AiSetting;
use App\Models\FoodGlycemicData;
use App\Services\AI\BedrockService;
use App\Services\SimulationCacheService;

class GlucoseCurveService
{
    public function __construct(
        private readonly ?BedrockService $bedrock = null,
    ) {}
    /**
     * Generate a time-dependent glucose response curve.
     *
     * @param  string  $foodItem       Name of food
     * @param  array   $snapshot       Digital twin snapshot_data
     * @param  string|null  $mealTime  Optional: 'morning','afternoon','evening','night'
     * @param  string|null  $quantity  Optional: quantity descriptor like '2 cups', '300g'
     * @return array{curve: array, peak: array, food: array, modifiers: array}
     */
    public function predict(string $foodItem, array $snapshot, ?string $mealTime = null, ?string $quantity = null): array
    {
        $food = SimulationCacheService::foodData($foodItem, fn () => FoodGlycemicData::findByName($foodItem));

        // Fallback estimation for unknown foods
        if (!$food) {
            $food = $this->estimateGlycemicData($foodItem);
        }

        $hp = $snapshot['health_profile'] ?? [];
        $baseline = $this->extractBaseline($snapshot);
        $modifiers = $this->computeModifiers($hp, $mealTime);
        $quantityMultiplier = $this->parseQuantityMultiplier($quantity, $food->serving_size ?? '1 serving');

        // Adjusted spike considering all cross-factors and quantity
        $adjustedSpike = $food->typical_spike_mg_dl * $modifiers['combined'] * $quantityMultiplier;
        $adjustedPeakTime = (int) round($food->peak_time_minutes * $modifiers['peak_time_factor']);
        $adjustedRecovery = (int) round($food->recovery_time_minutes * $modifiers['recovery_factor'] * min(1.5, $quantityMultiplier));

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
                'glycemic_load_adjusted' => round(($food->glycemic_load ?? 0) * $quantityMultiplier, 1),
                'category' => $food->category,
                'serving_size' => $food->serving_size,
                'quantity' => $quantity ?? '1 serving',
                'quantity_multiplier' => round($quantityMultiplier, 2),
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
     * Estimate glycemic data for unknown foods.
     * Uses AI (Bedrock) when available to get better estimates (A4),
     * falls back to conservative defaults.
     */
    private function estimateGlycemicData(string $foodItem): object
    {
        if ($this->bedrock && AiSetting::getValue('simulation_ai_explanation', true)) {
            $aiEstimate = $this->estimateWithAI($foodItem);
            if ($aiEstimate) {
                return $aiEstimate;
            }
        }

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

    /**
     * Use AI to estimate glycemic data for unknown foods (A4).
     */
    private function estimateWithAI(string $foodItem): ?object
    {
        $prompt = <<<'PROMPT'
You are a nutritional database. Given a food item, return ONLY a JSON object with these keys:
- "glycemic_index": integer 0-100
- "glycemic_load": integer per standard serving
- "typical_spike_mg_dl": typical blood sugar spike in mg/dL for a diabetic person
- "peak_time_minutes": minutes until peak glucose after eating
- "recovery_time_minutes": minutes to return near baseline
- "serving_size": standard serving description
- "category": one of "grain", "fruit", "vegetable", "dairy", "protein", "snack", "beverage", "sweet", "mixed"
- "alternatives": array of 3 healthier alternatives (strings)
Return ONLY valid JSON.
PROMPT;

        $result = $this->bedrock->ask($prompt, "Food item: {$foodItem}", [
            'max_tokens' => 200,
            'temperature' => 0.1,
        ]);

        if (!$result['success']) {
            return null;
        }

        $parsed = json_decode($result['response'], true);
        if (!is_array($parsed)) {
            if (preg_match('/\{.*\}/s', $result['response'], $matches)) {
                $parsed = json_decode($matches[0], true);
            }
        }

        if (!is_array($parsed) || !isset($parsed['glycemic_index'])) {
            return null;
        }

        // Clamp values to safe ranges
        return (object) [
            'food_item' => $foodItem,
            'category' => $parsed['category'] ?? 'unknown',
            'glycemic_index' => max(0, min(100, (int) $parsed['glycemic_index'])),
            'glycemic_load' => max(0, min(60, (int) ($parsed['glycemic_load'] ?? 15))),
            'typical_spike_mg_dl' => max(5, min(80, (int) ($parsed['typical_spike_mg_dl'] ?? 30))),
            'peak_time_minutes' => max(10, min(120, (int) ($parsed['peak_time_minutes'] ?? 45))),
            'recovery_time_minutes' => max(30, min(240, (int) ($parsed['recovery_time_minutes'] ?? 90))),
            'serving_size' => $parsed['serving_size'] ?? '1 serving',
            'alternatives' => array_slice($parsed['alternatives'] ?? [], 0, 5),
        ];
    }

    /**
     * Parse a quantity string into a multiplier relative to one standard serving.
     * Examples: '2 cups' → 2.0, '300g' → depends on serving size, '0.5 bowl' → 0.5
     */
    private function parseQuantityMultiplier(?string $quantity, string $standardServing): float
    {
        if ($quantity === null || trim($quantity) === '') {
            return 1.0;
        }

        $qty = strtolower(trim($quantity));

        // Extract leading number (supports decimals and fractions)
        if (preg_match('/^(\d+(?:\.\d+)?)/', $qty, $matches)) {
            $number = (float) $matches[1];

            // Check if standard serving also has a number to compare
            $standardNumber = 1.0;
            if (preg_match('/(\d+(?:\.\d+)?)/', strtolower($standardServing), $stdMatches)) {
                $standardNumber = (float) $stdMatches[1];
            }

            // If units match, compute ratio
            $qtyUnit = preg_replace('/^[\d.\s]+/', '', $qty);
            $stdUnit = preg_replace('/^[\d.\s]+/', '', strtolower($standardServing));

            if ($qtyUnit && $stdUnit && str_contains($stdUnit, $qtyUnit)) {
                return max(0.1, $number / max(0.01, $standardNumber));
            }

            // Weight-based conversion: grams
            if (str_contains($qty, 'g') && !str_contains($qty, 'glass')) {
                $grams = $number;
                $stdGrams = $this->extractGrams($standardServing);
                if ($stdGrams > 0) {
                    return max(0.1, $grams / $stdGrams);
                }
                // Default: assume 1 serving = 150g
                return max(0.1, $grams / 150.0);
            }

            // Generic numeric multiplier ("2 servings", "3 pieces", etc.)
            return max(0.1, min(5.0, $number));
        }

        // Descriptive quantities
        return match (true) {
            str_contains($qty, 'half') || str_contains($qty, '1/2') => 0.5,
            str_contains($qty, 'quarter') || str_contains($qty, '1/4') => 0.25,
            str_contains($qty, 'double') => 2.0,
            str_contains($qty, 'triple') => 3.0,
            str_contains($qty, 'large') || str_contains($qty, 'big') => 1.5,
            str_contains($qty, 'small') || str_contains($qty, 'little') => 0.6,
            default => 1.0,
        };
    }

    /**
     * Extract gram weight from a serving size string.
     */
    private function extractGrams(string $serving): float
    {
        if (preg_match('/(\d+(?:\.\d+)?)\s*g\b/', strtolower($serving), $matches)) {
            return (float) $matches[1];
        }
        return 0.0;
    }
}
