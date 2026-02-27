<?php

namespace Database\Factories;

use App\Enums\RiskCategory;
use App\Enums\SimulationType;
use App\Models\DigitalTwin;
use App\Models\Simulation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SimulationFactory extends Factory
{
    protected $model = Simulation::class;

    public function definition(): array
    {
        $originalRisk  = $this->faker->randomFloat(2, 30, 80);
        $simulatedRisk = $originalRisk + $this->faker->randomFloat(2, -10, 10);
        $simulatedRisk = max(0, min(100, $simulatedRisk));

        return [
            'user_id'              => User::factory(),
            'digital_twin_id'      => DigitalTwin::factory(),
            'type'                 => $this->faker->randomElement([SimulationType::MEAL, SimulationType::SLEEP, SimulationType::STRESS]),
            'input_data'           => ['type' => 'meal', 'description' => 'Test simulation'],
            'modified_twin_data'   => ['health_profile' => ['stress_level' => 'low']],
            'original_risk_score'  => $originalRisk,
            'simulated_risk_score' => $simulatedRisk,
            'risk_change'          => round($simulatedRisk - $originalRisk, 2),
            'risk_category_before' => RiskCategory::fromScore($originalRisk),
            'risk_category_after'  => RiskCategory::fromScore($simulatedRisk),
            'rag_explanation'      => $this->faker->paragraph(),
            'rag_confidence'       => $this->faker->randomFloat(2, 0.5, 1.0),
            'results'              => ['scores' => [], 'reasoning_path' => []],
        ];
    }
}
