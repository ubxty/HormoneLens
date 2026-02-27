<?php

namespace App\Services\Risk;

use App\Enums\RiskCategory;
use App\Models\DiseaseDiabetes;
use App\Models\DiseasePcod;
use App\Models\HealthProfile;

class RiskEngineService
{
    /**
     * Calculate metabolic risk score (0–100). Higher = worse.
     */
    public function calculateMetabolicRisk(
        HealthProfile $hp,
        ?DiseaseDiabetes $diabetes = null,
        ?DiseasePcod $pcod = null
    ): float {
        $score = 50.0;

        // Sleep factor
        if ($hp->avg_sleep_hours < 6) {
            $score += 15;
        } elseif ($hp->avg_sleep_hours < 7) {
            $score += 5;
        }

        // Stress factor
        $score += match ($hp->stress_level->value) {
            'high' => 20,
            'medium' => 10,
            default => 0,
        };

        // Physical activity
        $score += match ($hp->physical_activity->value) {
            'sedentary' => 15,
            'moderate' => 5,
            default => 0,
        };

        // Water intake
        if ($hp->water_intake < 2) {
            $score += 5;
        }

        // Diabetes-specific
        if ($diabetes) {
            if ($diabetes->avg_blood_sugar > 200) {
                $score += 20;
            } elseif ($diabetes->avg_blood_sugar > 140) {
                $score += 10;
            }
            if ($diabetes->family_history) {
                $score += 5;
            }
            if ($diabetes->sugar_cravings->value === 'frequent') {
                $score += 5;
            }
        }

        // PCOD-specific
        if ($pcod) {
            $score += match ($pcod->cycle_regularity->value) {
                'missed' => 15,
                'irregular' => 10,
                default => 0,
            };
            if ($pcod->insulin_resistance_diagnosed) {
                $score += 15;
            }
            if ($pcod->weight_gain_difficulty_losing) {
                $score += 10;
            }
        }

        return $this->clamp($score);
    }

    /**
     * Calculate insulin resistance score (0–100).
     */
    public function calculateInsulinResistance(
        HealthProfile $hp,
        ?DiseaseDiabetes $diabetes = null,
        ?DiseasePcod $pcod = null
    ): float {
        $score = 30.0;

        // BMI calculation (height in cm → m)
        $heightM = $hp->height / 100;
        $bmi = $heightM > 0 ? $hp->weight / ($heightM * $heightM) : 25;

        if ($bmi > 30) {
            $score += 25;
        } elseif ($bmi >= 25) {
            $score += 15;
        }

        if ($diabetes && $diabetes->avg_blood_sugar > 140) {
            $score += 20;
        }

        if ($pcod && $pcod->insulin_resistance_diagnosed) {
            $score += 25;
        }

        if ($hp->physical_activity->value === 'sedentary') {
            $score += 10;
        }

        $cravings = $diabetes?->sugar_cravings->value ?? $pcod?->sugar_cravings->value ?? null;
        if ($cravings === 'frequent') {
            $score += 10;
        }

        return $this->clamp($score);
    }

    /**
     * Calculate hormonal imbalance score (0–100).
     */
    public function calculateHormonalImbalance(
        HealthProfile $hp,
        ?DiseaseDiabetes $diabetes = null,
        ?DiseasePcod $pcod = null
    ): float {
        $score = 20.0;

        if ($pcod) {
            $symptoms = [
                $pcod->acne_oily_skin,
                $pcod->hair_thinning,
                $pcod->excess_facial_body_hair,
                $pcod->dark_skin_patches,
                $pcod->mood_swings_anxiety,
            ];
            $score += collect($symptoms)->filter()->count() * 10;

            if ($pcod->cycle_regularity->value === 'missed') {
                $score += 15;
            }
            if ($pcod->sleep_disturbances->value === 'often') {
                $score += 10;
            }
        }

        if ($diabetes) {
            if ($diabetes->energy_crashes_after_meals) {
                $score += 10;
            }
            if ($diabetes->fatigue->value === 'often') {
                $score += 10;
            }
        }

        if ($hp->stress_level->value === 'high') {
            $score += 15;
        }

        if ($hp->avg_sleep_hours < 6) {
            $score += 10;
        }

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
    public function calculateDietScore(
        HealthProfile $hp,
        ?DiseaseDiabetes $diabetes = null,
        ?DiseasePcod $pcod = null
    ): float {
        $score = 70.0;

        $cravings = $diabetes?->sugar_cravings->value ?? $pcod?->sugar_cravings->value ?? 'rare';
        $score -= match ($cravings) {
            'frequent' => 25,
            'occasional' => 10,
            default => 0,
        };

        if ($hp->water_intake < 2) {
            $score -= 10;
        }

        if ($diabetes && $diabetes->avg_blood_sugar > 200) {
            $score -= 20;
        } elseif ($diabetes && $diabetes->avg_blood_sugar > 140) {
            $score -= 10;
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
     */
    public function recalculateFromSnapshot(array $data): array
    {
        // Build temporary models from snapshot data
        $hp = new HealthProfile($data['health_profile'] ?? []);
        $diabetes = isset($data['diabetes']) ? new DiseaseDiabetes($data['diabetes']) : null;
        $pcod = isset($data['pcod']) ? new DiseasePcod($data['pcod']) : null;

        $metabolic = $this->calculateMetabolicRisk($hp, $diabetes, $pcod);
        $insulin = $this->calculateInsulinResistance($hp, $diabetes, $pcod);
        $hormonal = $this->calculateHormonalImbalance($hp, $diabetes, $pcod);
        $overall = $this->calculateOverallRisk($metabolic, $insulin, $hormonal);
        $sleepScore = $this->calculateSleepScore((float) ($data['health_profile']['avg_sleep_hours'] ?? 7));
        $stressScore = $this->calculateStressScore($data['health_profile']['stress_level'] ?? 'medium');
        $dietScore = $this->calculateDietScore($hp, $diabetes, $pcod);

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

    private function clamp(float $value, float $min = 0, float $max = 100): float
    {
        return max($min, min($max, $value));
    }
}
