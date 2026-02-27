<?php

namespace App\Services\Risk;

use App\Enums\RiskCategory;
use App\Models\Disease;
use App\Models\DiseaseField;
use App\Models\HealthProfile;

class RiskEngineService
{
    /**
     * Calculate metabolic risk score (0–100). Higher = worse.
     *
     * @param HealthProfile $hp
     * @param array<string, array> $diseaseDataMap  Keyed by disease slug, values are flat field arrays.
     */
    public function calculateMetabolicRisk(HealthProfile $hp, array $diseaseDataMap = []): float
    {
        $score = 50.0;

        // ── Health-profile factors (always applied) ──
        if ($hp->avg_sleep_hours < 6) {
            $score += 15;
        } elseif ($hp->avg_sleep_hours < 7) {
            $score += 5;
        }

        $stressVal = is_object($hp->stress_level) ? $hp->stress_level->value : ($hp->stress_level ?? 'medium');
        $score += match ($stressVal) {
            'high' => 20,
            'medium' => 10,
            default => 0,
        };

        $activityVal = is_object($hp->physical_activity) ? $hp->physical_activity->value : ($hp->physical_activity ?? 'moderate');
        $score += match ($activityVal) {
            'sedentary' => 15,
            'moderate' => 5,
            default => 0,
        };

        if ($hp->water_intake < 2) {
            $score += 5;
        }

        // ── Dynamic disease factors (metabolic score) ──
        $score += $this->computeDiseaseImpact($diseaseDataMap, 'metabolic');

        return $this->clamp($score);
    }

    /**
     * Calculate insulin resistance score (0–100).
     */
    public function calculateInsulinResistance(HealthProfile $hp, array $diseaseDataMap = []): float
    {
        $score = 30.0;

        // BMI
        $heightM = $hp->height / 100;
        $bmi = $heightM > 0 ? $hp->weight / ($heightM * $heightM) : 25;

        if ($bmi > 30) {
            $score += 25;
        } elseif ($bmi >= 25) {
            $score += 15;
        }

        $activityVal = is_object($hp->physical_activity) ? $hp->physical_activity->value : ($hp->physical_activity ?? 'moderate');
        if ($activityVal === 'sedentary') {
            $score += 10;
        }

        // ── Dynamic disease factors (insulin score) ──
        $score += $this->computeDiseaseImpact($diseaseDataMap, 'insulin');

        return $this->clamp($score);
    }

    /**
     * Calculate hormonal imbalance score (0–100).
     */
    public function calculateHormonalImbalance(HealthProfile $hp, array $diseaseDataMap = []): float
    {
        $score = 20.0;

        $stressVal = is_object($hp->stress_level) ? $hp->stress_level->value : ($hp->stress_level ?? 'medium');
        if ($stressVal === 'high') {
            $score += 15;
        }

        if ($hp->avg_sleep_hours < 6) {
            $score += 10;
        }

        // ── Dynamic disease factors (hormonal score) ──
        $score += $this->computeDiseaseImpact($diseaseDataMap, 'hormonal');

        return $this->clamp($score);
    }

    /**
     * Categorize a score into a risk category.
     */
    public function categorizeRisk(float $score): RiskCategory
    {
        return RiskCategory::fromScore($score);
    }

    /**
     * Calculate sleep score (0–100, higher = better).
     */
    public function calculateSleepScore(float $sleepHours): float
    {
        if ($sleepHours >= 7 && $sleepHours <= 9) {
            return 100.0;
        }
        if ($sleepHours >= 6) {
            return 70.0;
        }
        if ($sleepHours >= 5) {
            return 40.0;
        }
        return max(0, 20.0);
    }

    /**
     * Calculate stress score (0–100, higher = better / lower stress).
     */
    public function calculateStressScore(string $stressLevel): float
    {
        return match ($stressLevel) {
            'low' => 90.0,
            'medium' => 55.0,
            'high' => 20.0,
            default => 50.0,
        };
    }

    /**
     * Calculate diet score based on available factors (0–100, higher = better).
     */
    public function calculateDietScore(HealthProfile $hp, array $diseaseDataMap = []): float
    {
        $score = 70.0;

        if ($hp->water_intake < 2) {
            $score -= 10;
        }

        // Look for sugar_cravings across any disease data
        foreach ($diseaseDataMap as $data) {
            $cravings = $data['sugar_cravings'] ?? null;
            if ($cravings) {
                $score -= match ($cravings) {
                    'frequent' => 25,
                    'occasional' => 10,
                    default => 0,
                };
                break; // Use first disease that has cravings
            }
        }

        // Look for blood sugar values across disease data
        $bloodSugar = $diseaseDataMap['diabetes']['avg_blood_sugar'] ?? null;
        if ($bloodSugar !== null) {
            if ($bloodSugar > 200) {
                $score -= 20;
            } elseif ($bloodSugar > 140) {
                $score -= 10;
            }
        }

        return $this->clamp($score);
    }

    /**
     * Calculate overall risk score (weighted combination).
     */
    public function calculateOverallRisk(float $metabolic, float $insulin, float $hormonal): float
    {
        return $this->clamp(($metabolic * 0.4) + ($insulin * 0.3) + ($hormonal * 0.3));
    }

    /**
     * Recalculate all scores from a snapshot data array (used during simulations).
     *
     * Snapshot format: ['health_profile' => [...], 'diabetes' => [...], 'pcod' => [...], ...]
     * Every key except 'health_profile' is treated as a disease slug with its field values.
     */
    public function recalculateFromSnapshot(array $data): array
    {
        $hp = new HealthProfile($data['health_profile'] ?? []);

        // Build disease data map: everything except 'health_profile'
        $diseaseDataMap = [];
        foreach ($data as $key => $value) {
            if ($key !== 'health_profile' && is_array($value)) {
                $diseaseDataMap[$key] = $value;
            }
        }

        $metabolic = $this->calculateMetabolicRisk($hp, $diseaseDataMap);
        $insulin = $this->calculateInsulinResistance($hp, $diseaseDataMap);
        $hormonal = $this->calculateHormonalImbalance($hp, $diseaseDataMap);
        $overall = $this->calculateOverallRisk($metabolic, $insulin, $hormonal);
        $sleepScore = $this->calculateSleepScore((float) ($data['health_profile']['avg_sleep_hours'] ?? 7));
        $stressScore = $this->calculateStressScore($data['health_profile']['stress_level'] ?? 'medium');
        $dietScore = $this->calculateDietScore($hp, $diseaseDataMap);

        return [
            'metabolic_health_score' => round($metabolic, 2),
            'insulin_resistance_score' => round($insulin, 2),
            'sleep_score' => round($sleepScore, 2),
            'stress_score' => round($stressScore, 2),
            'diet_score' => round($dietScore, 2),
            'overall_risk_score' => round($overall, 2),
            'risk_category' => $this->categorizeRisk($overall)->value,
        ];
    }

    // ── Private helpers ──────────────────────────────

    /**
     * Compute total risk impact for a specific score type from all disease data.
     * Reads risk_config from disease_fields table dynamically.
     */
    private function computeDiseaseImpact(array $diseaseDataMap, string $scoreType): float
    {
        $totalImpact = 0.0;

        foreach ($diseaseDataMap as $diseaseSlug => $fieldValues) {
            if (!is_array($fieldValues)) {
                continue;
            }

            $disease = Disease::where('slug', $diseaseSlug)->first();
            if (!$disease) {
                continue;
            }

            $fields = DiseaseField::where('disease_id', $disease->id)
                ->whereNotNull('risk_config')
                ->get();

            foreach ($fields as $field) {
                $config = $field->risk_config;
                if (($config['score'] ?? null) !== $scoreType) {
                    continue;
                }

                $userValue = $fieldValues[$field->slug] ?? null;
                if ($userValue === null) {
                    continue;
                }

                foreach ($config['rules'] ?? [] as $rule) {
                    if ($this->evaluateRule($userValue, $rule)) {
                        $totalImpact += (float) ($rule['impact'] ?? 0);
                        break; // First matching rule wins per field
                    }
                }
            }
        }

        return $totalImpact;
    }

    /**
     * Evaluate a single risk rule against a user value.
     */
    private function evaluateRule(mixed $userValue, array $rule): bool
    {
        $operator = $rule['operator'] ?? '==';
        $ruleValue = $rule['value'] ?? null;

        return match ($operator) {
            '==' => $userValue == $ruleValue,
            '!=' => $userValue != $ruleValue,
            '>' => is_numeric($userValue) && $userValue > $ruleValue,
            '>=' => is_numeric($userValue) && $userValue >= $ruleValue,
            '<' => is_numeric($userValue) && $userValue < $ruleValue,
            '<=' => is_numeric($userValue) && $userValue <= $ruleValue,
            'in' => is_array($ruleValue) && in_array($userValue, $ruleValue),
            default => false,
        };
    }

    private function clamp(float $value, float $min = 0, float $max = 100): float
    {
        return max($min, min($max, $value));
    }
}
