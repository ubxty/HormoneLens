<?php

namespace App\Services\Simulation;

use App\Contracts\RagSearchInterface;
use App\Enums\SimulationType;
use App\Models\AiSetting;
use App\Models\Simulation;
use App\Models\User;
use App\Repositories\SimulationRepository;
use App\Services\AI\BedrockService;
use App\Services\AI\PromptTemplates;
use App\Services\Alerts\AlertService;
use App\Services\DigitalTwin\DigitalTwinService;
use App\Services\Risk\RiskEngineService;
use App\Services\Simulation\GlucoseCurveService;

class SimulationService
{
    public function __construct(
        private readonly DigitalTwinService $twinService,
        private readonly RiskEngineService $riskEngine,
        private readonly AlertService $alertService,
        private readonly RagSearchInterface $ragSearch,
        private readonly SimulationRepository $simulationRepo,
        private readonly BedrockService $bedrock,
        private readonly GlucoseCurveService $glucoseCurve,
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

        // Generate AI-enhanced explanation
        $aiExplanation = $this->generateAIExplanation($type, $input, $originalRisk, $simulatedRisk, $ragResult);

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
            'rag_explanation' => $aiExplanation['success'] ? $aiExplanation['response'] : ($ragResult['answer'] ?? 'No explanation available.'),
            'rag_confidence' => $ragResult['confidence'],
            'results' => [
                'scores' => $newScores,
                'reasoning_path' => $ragResult['reasoning_path'],
                'ai_metadata' => $aiExplanation['success'] ? [
                    'model'  => $aiExplanation['model_used'],
                    'tokens' => $aiExplanation['input_tokens'] + $aiExplanation['output_tokens'],
                    'cost'   => $aiExplanation['cost'],
                ] : null,
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
        $mealTime = $input['meal_time'] ?? null;

        // Generate glucose curve prediction with cross-factor interactions
        $curveResult = $this->glucoseCurve->predict($foodItem, $snapshotData, $mealTime);

        // Get RAG explanation for food impact
        $ragResult = $this->ragSearch->search(
            "impact of {$foodItem} on {$diseaseContext} blood sugar insulin hormones",
            $diseaseContext
        );

        // Apply food-specific modifiers using curve data
        $modifiedData = $this->applyFoodModifier($snapshotData, $foodItem, $ragResult, $curveResult);

        // Recalculate risk
        $newScores = $this->riskEngine->recalculateFromSnapshot($modifiedData);
        $originalRisk = (float) $twin->overall_risk_score;
        $simulatedRisk = (float) $newScores['overall_risk_score'];
        $riskChange = round($simulatedRisk - $originalRisk, 2);

        // Build alternatives (AI-enhanced when available)
        $aiFoodAnalysis = $this->generateFoodAnalysis($foodItem, $diseaseContext, $ragResult);
        $dbAlternatives = $curveResult['food']['alternatives'] ?? [];
        $alternatives = $aiFoodAnalysis['success']
            ? $this->parseAIAlternatives($aiFoodAnalysis['response'], $foodItem)
            : (!empty($dbAlternatives) ? $dbAlternatives : $this->buildFoodAlternatives($foodItem));

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
            'rag_explanation' => $aiFoodAnalysis['success'] ? $aiFoodAnalysis['response'] : $ragResult['answer'],
            'rag_confidence' => $ragResult['confidence'],
            'results' => [
                'scores' => $newScores,
                'alternatives' => $alternatives,
                'reasoning_path' => $ragResult['reasoning_path'],
                'glucose_curve' => $curveResult['curve'],
                'peak' => $curveResult['peak'],
                'recovery_minutes' => $curveResult['recovery_minutes'],
                'baseline_mg_dl' => $curveResult['baseline_mg_dl'],
                'food_data' => $curveResult['food'],
                'modifiers' => $curveResult['modifiers'],
                'ai_metadata' => $aiFoodAnalysis['success'] ? [
                    'model'  => $aiFoodAnalysis['model_used'],
                    'tokens' => $aiFoodAnalysis['input_tokens'] + $aiFoodAnalysis['output_tokens'],
                    'cost'   => $aiFoodAnalysis['cost'],
                ] : null,
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
     * Disease data keys are dynamic slugs — modifiers affect any disease that has the relevant field.
     */
    private function applyLifestyleModifier(array $snapshot, SimulationType $type, array $input): array
    {
        $modified = $snapshot;

        switch ($type) {
            case SimulationType::MEAL:
                $description = strtolower($input['description'] ?? '');
                if (str_contains($description, 'reduce sugar') || str_contains($description, 'less sugar')) {
                    // Positive change: improve sugar cravings across all diseases
                    foreach ($modified as $key => &$data) {
                        if ($key !== 'health_profile' && is_array($data) && isset($data['sugar_cravings'])) {
                            $data['sugar_cravings'] = 'rare';
                        }
                    }
                    unset($data);
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
     * Works dynamically across all disease data in the snapshot.
     */
    private function applyFoodModifier(array $snapshot, string $foodItem, array $ragResult, array $curveResult = []): array
    {
        $modified = $snapshot;
        $foodLower = strtolower($foodItem);

        // Use curve data for glycemic classification
        $gi = $curveResult['food']['glycemic_index'] ?? null;
        $spike = $curveResult['peak']['glucose_mg_dl'] ?? null;
        $baseline = $curveResult['baseline_mg_dl'] ?? 100;

        if ($gi !== null) {
            $isHighGi = $gi >= 60;
            $isLowGi = $gi <= 45;
            $adjustedSpike = $spike ? ($spike - $baseline) : ($isHighGi ? 40 : ($isLowGi ? -15 : 10));
        } else {
            // Legacy fallback for hardcoded foods
            $highGiFoods = ['white rice', 'sugar', 'candy', 'soda', 'white bread', 'potato', 'fries',
                'pastry', 'cake', 'jalebi', 'gulab jamun', 'maida', 'pizza', 'naan'];
            $lowGiFoods = ['brown rice', 'quinoa', 'oats', 'salad', 'vegetables', 'dal', 'lentils',
                'sprouts', 'nuts', 'yogurt', 'curd'];

            $isHighGi = false;
            $isLowGi = false;
            foreach ($highGiFoods as $food) {
                if (str_contains($foodLower, $food)) { $isHighGi = true; break; }
            }
            foreach ($lowGiFoods as $food) {
                if (str_contains($foodLower, $food)) { $isLowGi = true; break; }
            }
            $adjustedSpike = $isHighGi ? 40 : ($isLowGi ? -15 : 10);
        }

        // Apply blood sugar modifiers to any disease that has avg_blood_sugar
        foreach ($modified as $key => &$data) {
            if ($key === 'health_profile' || !is_array($data)) {
                continue;
            }

            if (isset($data['avg_blood_sugar'])) {
                $newSugar = ($data['avg_blood_sugar'] ?? 120) + $adjustedSpike;
                $data['avg_blood_sugar'] = (float) min(350, max(70, $newSugar));
                if ($isHighGi) {
                    $data['sugar_cravings'] = $data['sugar_cravings'] ?? 'frequent';
                } elseif ($isLowGi && isset($data['sugar_cravings'])) {
                    $data['sugar_cravings'] = 'rare';
                }
            }

            if (!isset($data['avg_blood_sugar']) && isset($data['sugar_cravings'])) {
                if ($isHighGi) {
                    $data['sugar_cravings'] = 'frequent';
                } elseif ($isLowGi) {
                    $data['sugar_cravings'] = 'rare';
                }
            }
        }
        unset($data);

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

    /**
     * Generate AI-enhanced explanation for lifestyle simulation.
     */
    private function generateAIExplanation(SimulationType $type, array $input, float $originalRisk, float $simulatedRisk, array $ragResult): array
    {
        if (!AiSetting::getValue('simulation_ai_explanation', true)) {
            return $this->bedrock->ask('', ''); // returns errorResult since AI disabled
        }

        $systemPrompt = PromptTemplates::simulationExplanation();
        $userMessage = "Simulation Type: {$type->value}"
            . "\nChanges Made: " . json_encode($input)
            . "\nRisk Score Change: {$originalRisk} → {$simulatedRisk}"
            . "\nKnowledge Base Context: " . ($ragResult['answer'] ?? 'No context available');

        return $this->bedrock->ask($systemPrompt, $userMessage);
    }

    /**
     * Generate AI-enhanced food impact analysis.
     */
    private function generateFoodAnalysis(string $foodItem, ?string $diseaseContext, array $ragResult): array
    {
        if (!AiSetting::getValue('simulation_ai_explanation', true)) {
            return ['success' => false, 'response' => ''];
        }

        $systemPrompt = PromptTemplates::foodImpact();
        $userMessage = "Food Item: {$foodItem}"
            . "\nCondition: " . ($diseaseContext ?? 'general hormonal health')
            . "\nKnowledge Base Context: " . ($ragResult['answer'] ?? 'No context available')
            . "\n\nProvide: 1) Impact analysis 2) Three healthier alternatives as a JSON array under key 'alternatives'";

        return $this->bedrock->ask($systemPrompt, $userMessage);
    }

    /**
     * Parse AI-generated food alternatives, falling back to static list.
     */
    private function parseAIAlternatives(string $aiResponse, string $foodItem): array
    {
        if (preg_match('/\[.*?\]/s', $aiResponse, $matches)) {
            $parsed = json_decode($matches[0], true);
            if (is_array($parsed) && count($parsed) > 0) {
                return array_slice($parsed, 0, 5);
            }
        }

        return $this->buildFoodAlternatives($foodItem);
    }
}
