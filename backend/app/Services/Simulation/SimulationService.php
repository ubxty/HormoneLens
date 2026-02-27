<?php

namespace App\Services\Simulation;

use App\Contracts\RagSearchInterface;
use App\Enums\SimulationType;
use App\Models\Simulation;
use App\Models\User;
use App\Repositories\SimulationRepository;
use App\Services\Alerts\AlertService;
use App\Services\DigitalTwin\DigitalTwinService;
use App\Services\Risk\RiskEngineService;

class SimulationService
{
    public function __construct(
        private readonly DigitalTwinService $twinService,
        private readonly RiskEngineService $riskEngine,
        private readonly AlertService $alertService,
        private readonly RagSearchInterface $ragSearch,
        private readonly SimulationRepository $simulationRepo,
    ) {}

    /**
     * Simulate a lifestyle change (meal, sleep, stress).
     */
    public function simulateLifestyleChange(User $user, array $input): Simulation
    {
        $twin = $this->twinService->getActive($user);
        if (!$twin) {
            throw new \RuntimeException('No active Digital Twin found. Please generate one first.');
        }

        $type = SimulationType::from($input['type']);
        $snapshotData = $twin->snapshot_data;

        // Clone and modify snapshot based on simulation type
        $modifiedData = $this->applyLifestyleModifier($snapshotData, $type, $input);

        // Recalculate risk with modified data
        $newScores = $this->riskEngine->recalculateFromSnapshot($modifiedData);
        $originalRisk = (float) $twin->overall_risk_score;
        $simulatedRisk = (float) $newScores['overall_risk_score'];
        $riskChange = round($simulatedRisk - $originalRisk, 2);

        // Get RAG explanation
        $diseaseContext = $snapshotData['health_profile']['disease_type'] ?? null;
        $ragResult = $this->ragSearch->search($input['description'] ?? $input['type'], $diseaseContext);

        // Store simulation
        $simulation = $this->simulationRepo->create([
            'user_id' => $user->id,
            'digital_twin_id' => $twin->id,
            'type' => $type->value,
            'input_data' => $input,
            'modified_twin_data' => $modifiedData,
            'original_risk_score' => $originalRisk,
            'simulated_risk_score' => $simulatedRisk,
            'risk_change' => $riskChange,
            'risk_category_before' => $twin->risk_category->value,
            'risk_category_after' => $newScores['risk_category'],
            'rag_explanation' => $ragResult['answer'],
            'rag_confidence' => $ragResult['confidence'],
            'results' => [
                'scores' => $newScores,
                'reasoning_path' => $ragResult['reasoning_path'],
            ],
        ]);

        // Evaluate alerts
        $this->alertService->evaluate($user, [
            'simulated_risk_score' => $simulatedRisk,
            'input_data' => $input,
            'modified_twin_data' => $modifiedData,
            'type' => $type->value,
            'rag_explanation' => $ragResult['answer'],
        ], $simulation->id);

        return $simulation->load('alerts');
    }

    /**
     * Simulate the impact of a specific food item.
     */
    public function simulateFoodImpact(User $user, array $input): Simulation
    {
        $twin = $this->twinService->getActive($user);
        if (!$twin) {
            throw new \RuntimeException('No active Digital Twin found. Please generate one first.');
        }

        $snapshotData = $twin->snapshot_data;
        $diseaseContext = $snapshotData['health_profile']['disease_type'] ?? null;
        $foodItem = $input['food_item'];

        // Get RAG explanation for food impact
        $ragResult = $this->ragSearch->search(
            "impact of {$foodItem} on {$diseaseContext} blood sugar insulin hormones",
            $diseaseContext
        );

        // Apply food-specific modifiers
        $modifiedData = $this->applyFoodModifier($snapshotData, $foodItem, $ragResult);

        // Recalculate risk
        $newScores = $this->riskEngine->recalculateFromSnapshot($modifiedData);
        $originalRisk = (float) $twin->overall_risk_score;
        $simulatedRisk = (float) $newScores['overall_risk_score'];
        $riskChange = round($simulatedRisk - $originalRisk, 2);

        // Build alternatives
        $alternatives = $this->buildFoodAlternatives($foodItem);

        // Store simulation
        $simulation = $this->simulationRepo->create([
            'user_id' => $user->id,
            'digital_twin_id' => $twin->id,
            'type' => SimulationType::FOOD_IMPACT->value,
            'input_data' => $input,
            'modified_twin_data' => $modifiedData,
            'original_risk_score' => $originalRisk,
            'simulated_risk_score' => $simulatedRisk,
            'risk_change' => $riskChange,
            'risk_category_before' => $twin->risk_category->value,
            'risk_category_after' => $newScores['risk_category'],
            'rag_explanation' => $ragResult['answer'],
            'rag_confidence' => $ragResult['confidence'],
            'results' => [
                'scores' => $newScores,
                'alternatives' => $alternatives,
                'reasoning_path' => $ragResult['reasoning_path'],
            ],
        ]);

        // Evaluate alerts
        $this->alertService->evaluate($user, [
            'simulated_risk_score' => $simulatedRisk,
            'input_data' => $input,
            'modified_twin_data' => $modifiedData,
            'type' => 'food_impact',
            'rag_explanation' => $ragResult['answer'],
        ], $simulation->id);

        return $simulation->load('alerts');
    }

    /**
     * Apply lifestyle modifier to snapshot data based on simulation type.
     */
    private function applyLifestyleModifier(array $snapshot, SimulationType $type, array $input): array
    {
        $modified = $snapshot;

        switch ($type) {
            case SimulationType::MEAL:
                // Simulating dietary change — adjust diet-related factors
                $description = strtolower($input['description'] ?? '');
                if (str_contains($description, 'reduce sugar') || str_contains($description, 'less sugar')) {
                    // Positive change: improve sugar cravings
                    if (isset($modified['diabetes']['sugar_cravings'])) {
                        $modified['diabetes']['sugar_cravings'] = 'rare';
                    }
                    if (isset($modified['pcod']['sugar_cravings'])) {
                        $modified['pcod']['sugar_cravings'] = 'rare';
                    }
                }
                if (str_contains($description, 'more vegetables') || str_contains($description, 'balanced diet')) {
                    $modified['health_profile']['eating_habits'] = 'balanced diet with vegetables and whole grains';
                }
                break;

            case SimulationType::SLEEP:
                $sleepHours = $input['parameters']['sleep_hours'] ?? 8;
                $modified['health_profile']['avg_sleep_hours'] = (float) $sleepHours;
                break;

            case SimulationType::STRESS:
                $stressLevel = $input['parameters']['stress_level'] ?? 'low';
                $modified['health_profile']['stress_level'] = $stressLevel;
                break;
        }

        return $modified;
    }

    /**
     * Apply food-specific modifiers based on glycemic impact estimation.
     */
    private function applyFoodModifier(array $snapshot, string $foodItem, array $ragResult): array
    {
        $modified = $snapshot;
        $foodLower = strtolower($foodItem);

        // High glycemic foods increase blood sugar
        $highGiFoods = ['white rice', 'sugar', 'candy', 'soda', 'white bread', 'potato', 'fries',
            'pastry', 'cake', 'jalebi', 'gulab jamun', 'maida', 'pizza', 'naan'];
        $lowGiFoods = ['brown rice', 'quinoa', 'oats', 'salad', 'vegetables', 'dal', 'lentils',
            'sprouts', 'nuts', 'yogurt', 'curd'];

        $isHighGi = false;
        $isLowGi = false;

        foreach ($highGiFoods as $food) {
            if (str_contains($foodLower, $food)) {
                $isHighGi = true;
                break;
            }
        }
        foreach ($lowGiFoods as $food) {
            if (str_contains($foodLower, $food)) {
                $isLowGi = true;
                break;
            }
        }

        if ($isHighGi && isset($modified['diabetes'])) {
            $modified['diabetes']['avg_blood_sugar'] = min(350, ($modified['diabetes']['avg_blood_sugar'] ?? 120) + 40);
            $modified['diabetes']['sugar_cravings'] = 'frequent';
        } elseif ($isLowGi && isset($modified['diabetes'])) {
            $modified['diabetes']['avg_blood_sugar'] = max(70, ($modified['diabetes']['avg_blood_sugar'] ?? 120) - 15);
            $modified['diabetes']['sugar_cravings'] = 'rare';
        }

        return $modified;
    }

    /**
     * Build healthier food alternatives based on the food item.
     */
    private function buildFoodAlternatives(string $foodItem): array
    {
        $alternatives = [
            'white rice' => ['Brown rice', 'Quinoa', 'Cauliflower rice'],
            'sugar' => ['Stevia', 'Jaggery (in moderation)', 'Honey (small amount)'],
            'white bread' => ['Whole wheat bread', 'Multigrain roti', 'Oat bread'],
            'potato' => ['Sweet potato', 'Cauliflower', 'Turnip'],
            'naan' => ['Whole wheat roti', 'Bajra roti', 'Jowar roti'],
            'pizza' => ['Whole wheat base pizza with vegetables', 'Stuffed roti', 'Vegetable wrap'],
            'soda' => ['Lemon water', 'Buttermilk (chaas)', 'Green tea'],
            'candy' => ['Dates', 'Dark chocolate (85%+)', 'Mixed nuts'],
            'maida' => ['Whole wheat flour (atta)', 'Bajra flour', 'Ragi flour'],
            'fries' => ['Baked sweet potato wedges', 'Air-fried vegetables', 'Roasted chickpeas'],
        ];

        $foodLower = strtolower($foodItem);
        foreach ($alternatives as $key => $alts) {
            if (str_contains($foodLower, $key)) {
                return $alts;
            }
        }

        return ['Consider whole grain alternatives', 'Add more vegetables to your meal', 'Choose low-glycemic options'];
    }
}
