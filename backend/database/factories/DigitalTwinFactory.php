<?php

namespace Database\Factories;

use App\Enums\RiskCategory;
use App\Models\DigitalTwin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DigitalTwinFactory extends Factory
{
    protected $model = DigitalTwin::class;

    public function definition(): array
    {
        $overallRisk = $this->faker->randomFloat(2, 20, 90);

        return [
            'user_id'                  => User::factory(),
            'metabolic_health_score'   => $this->faker->randomFloat(2, 3, 9),
            'insulin_resistance_score' => $this->faker->randomFloat(2, 2, 8),
            'sleep_score'              => $this->faker->randomFloat(2, 3, 9),
            'stress_score'             => $this->faker->randomFloat(2, 2, 8),
            'diet_score'               => $this->faker->randomFloat(2, 3, 9),
            'overall_risk_score'       => $overallRisk,
            'risk_category'            => RiskCategory::fromScore($overallRisk),
            'snapshot_data'            => [
                'health_profile' => [
                    'disease_type'    => 'diabetes',
                    'avg_sleep_hours' => 7,
                    'stress_level'    => 'medium',
                    'eating_habits'   => 'moderate',
                ],
                'diabetes' => [
                    'avg_blood_sugar' => 160,
                    'sugar_cravings'  => 'frequent',
                ],
            ],
            'is_active' => true,
        ];
    }
}
