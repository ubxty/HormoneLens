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
use App\Services\Prediction\CortisolPredictionService;
use App\Services\Prediction\CyclePredictionService;

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
        private readonly CortisolPredictionService $cortisolPrediction,
        private readonly CyclePredictionService $cyclePrediction,
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
                'predictions' => $this->generatePredictions($modifiedData),
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
     * Simulate from an existing snapshot (AR3: chained simulations).
     * Instead of using the active twin, uses the provided snapshot as base.
     */
    public function simulateFromSnapshot(User $user, array $input, array $baseSnapshot, float $baseRiskScore, int $parentSimId): Simulation
    {
        $twin = $this->twinService->getActive($user);
        if (!$twin) {
            throw new \RuntimeException('No active Digital Twin found. Please generate one first.');
        }

        $type = SimulationType::from($input['type']);
        $input['parent_simulation_id'] = $parentSimId;

        $modifiedData = $this->applyLifestyleModifier($baseSnapshot, $type, $input);
        $newScores = $this->riskEngine->recalculateFromSnapshot($modifiedData);

        $originalRisk = $baseRiskScore;
        $simulatedRisk = (float) $newScores['overall_risk_score'];
        $riskChange = round($simulatedRisk - $originalRisk, 2);

        $diseaseContext = $baseSnapshot['health_profile']['disease_type'] ?? null;
        $ragResult = $this->ragSearch->search($input['description'] ?? $input['type'], $diseaseContext);
        $aiExplanation = $this->generateAIExplanation($type, $input, $originalRisk, $simulatedRisk, $ragResult);

        $simulation = $this->simulationRepo->create([
            'user_id' => $user->id,
            'digital_twin_id' => $twin->id,
            'type' => $type->value,
            'input_data' => $input,
            'modified_twin_data' => $modifiedData,
            'original_risk_score' => $originalRisk,
            'simulated_risk_score' => $simulatedRisk,
            'risk_change' => $riskChange,
            'risk_category_before' => $this->riskEngine->categorizeRisk($originalRisk)->value,
            'risk_category_after' => $newScores['risk_category'],
            'rag_explanation' => $aiExplanation['success'] ? $aiExplanation['response'] : ($ragResult['answer'] ?? 'No explanation available.'),
            'rag_confidence' => $ragResult['confidence'],
            'results' => [
                'scores' => $newScores,
                'reasoning_path' => $ragResult['reasoning_path'],
                'predictions' => $this->generatePredictions($modifiedData),
                'chained_from' => $parentSimId,
            ],
        ]);

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
        $quantity = $input['quantity'] ?? null;

        // Generate glucose curve prediction with cross-factor interactions
        $curveResult = $this->glucoseCurve->predict($foodItem, $snapshotData, $mealTime, $quantity);

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
                'predictions' => $this->generatePredictions($modifiedData),
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
                $mealParams = $input['parameters'] ?? [];
                $applied = false;

                // Keyword-based modifiers
                if (str_contains($description, 'reduce sugar') || str_contains($description, 'less sugar') || str_contains($description, 'no sugar') || str_contains($description, 'cut sugar')) {
                    foreach ($modified as $key => &$data) {
                        if ($key !== 'health_profile' && is_array($data) && isset($data['sugar_cravings'])) {
                            $data['sugar_cravings'] = 'rare';
                        }
                    }
                    unset($data);
                    $applied = true;
                }
                if (str_contains($description, 'more sugar') || str_contains($description, 'extra sugar') || str_contains($description, 'sweet') || str_contains($description, 'dessert')) {
                    foreach ($modified as $key => &$data) {
                        if ($key !== 'health_profile' && is_array($data) && isset($data['sugar_cravings'])) {
                            $data['sugar_cravings'] = 'frequent';
                        }
                    }
                    unset($data);
                    $applied = true;
                }
                if (str_contains($description, 'more vegetables') || str_contains($description, 'balanced diet') || str_contains($description, 'healthy') || str_contains($description, 'salad') || str_contains($description, 'fruits')) {
                    $modified['health_profile']['eating_habits'] = 'balanced diet with vegetables and whole grains';
                    $applied = true;
                }
                if (str_contains($description, 'skip meal') || str_contains($description, 'fasting') || str_contains($description, 'skip breakfast') || str_contains($description, 'skip lunch')) {
                    $modified['health_profile']['eating_habits'] = 'irregular meals with skipped meals';
                    foreach ($modified as $key => &$data) {
                        if ($key !== 'health_profile' && is_array($data) && isset($data['avg_blood_sugar'])) {
                            $data['avg_blood_sugar'] = max(70, ($data['avg_blood_sugar'] ?? 120) - 15);
                        }
                    }
                    unset($data);
                    $applied = true;
                }
                if (str_contains($description, 'junk food') || str_contains($description, 'fast food') || str_contains($description, 'fried') || str_contains($description, 'processed')) {
                    $modified['health_profile']['eating_habits'] = 'high processed and fried food intake';
                    foreach ($modified as $key => &$data) {
                        if ($key !== 'health_profile' && is_array($data) && isset($data['avg_blood_sugar'])) {
                            $data['avg_blood_sugar'] = min(350, ($data['avg_blood_sugar'] ?? 120) + 30);
                        }
                        if ($key !== 'health_profile' && is_array($data) && isset($data['sugar_cravings'])) {
                            $data['sugar_cravings'] = 'frequent';
                        }
                    }
                    unset($data);
                    $applied = true;
                }
                if (str_contains($description, 'high protein') || str_contains($description, 'protein rich') || str_contains($description, 'eggs') || str_contains($description, 'chicken') || str_contains($description, 'paneer') || str_contains($description, 'dal')) {
                    $modified['health_profile']['eating_habits'] = 'high protein balanced diet';
                    $applied = true;
                }
                if (str_contains($description, 'low carb') || str_contains($description, 'keto') || str_contains($description, 'no carbs')) {
                    foreach ($modified as $key => &$data) {
                        if ($key !== 'health_profile' && is_array($data) && isset($data['avg_blood_sugar'])) {
                            $data['avg_blood_sugar'] = max(70, ($data['avg_blood_sugar'] ?? 120) - 20);
                        }
                    }
                    unset($data);
                    $modified['health_profile']['eating_habits'] = 'low carbohydrate diet';
                    $applied = true;
                }

                // If no keyword matched, use AI to interpret the meal description
                if (!$applied && !empty($description)) {
                    $aiInterpretation = $this->interpretMealWithAI($description);
                    if ($aiInterpretation) {
                        if (isset($aiInterpretation['eating_habits'])) {
                            $modified['health_profile']['eating_habits'] = $aiInterpretation['eating_habits'];
                        }
                        if (isset($aiInterpretation['sugar_impact'])) {
                            $impact = $aiInterpretation['sugar_impact'];
                            foreach ($modified as $key => &$data) {
                                if ($key !== 'health_profile' && is_array($data)) {
                                    if (isset($data['avg_blood_sugar']) && is_numeric($impact)) {
                                        $data['avg_blood_sugar'] = (float) min(350, max(70, ($data['avg_blood_sugar'] ?? 120) + $impact));
                                    }
                                    if (isset($data['sugar_cravings']) && isset($aiInterpretation['cravings'])) {
                                        $data['sugar_cravings'] = $aiInterpretation['cravings'];
                                    }
                                }
                            }
                            unset($data);
                        }
                    }
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

            case SimulationType::ACTIVITY:
                $activityLevel = $input['parameters']['activity_level'] ?? 'moderate';
                $modified['health_profile']['physical_activity'] = $activityLevel;
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

        // Use curve data for glycemic classification — always available from GlucoseCurveService
        $gi = $curveResult['food']['glycemic_index'] ?? 55;
        $gl = $curveResult['food']['glycemic_load_adjusted'] ?? $curveResult['food']['glycemic_load'] ?? 15;
        $spike = $curveResult['peak']['glucose_mg_dl'] ?? null;
        $baseline = $curveResult['baseline_mg_dl'] ?? 100;

        $isHighGi = $gi >= 60;
        $isLowGi = $gi <= 45;

        // Use glycemic load (GL) for spike calculation when available (S8: GL sensitivity)
        // GL better represents actual blood sugar impact than GI alone
        $adjustedSpike = $spike
            ? ($spike - $baseline)
            : $this->estimateSpikeFromGL($gl, $gi);

        // Apply blood sugar modifiers to any disease that has avg_blood_sugar
        foreach ($modified as $key => &$data) {
            if ($key === 'health_profile' || !is_array($data)) {
                continue;
            }

            if (isset($data['avg_blood_sugar'])) {
                $newSugar = ($data['avg_blood_sugar'] ?? 120) + $adjustedSpike;
                $data['avg_blood_sugar'] = (float) min(350, max(70, $newSugar));
                if ($gl >= 20 || $isHighGi) {
                    $data['sugar_cravings'] = $data['sugar_cravings'] ?? 'frequent';
                } elseif (($gl <= 10 && $isLowGi) && isset($data['sugar_cravings'])) {
                    $data['sugar_cravings'] = 'rare';
                }
            }

            if (!isset($data['avg_blood_sugar']) && isset($data['sugar_cravings'])) {
                if ($gl >= 20 || $isHighGi) {
                    $data['sugar_cravings'] = 'frequent';
                } elseif ($gl <= 10 && $isLowGi) {
                    $data['sugar_cravings'] = 'rare';
                }
            }
        }
        unset($data);

        return $modified;
    }

    /**
     * Estimate blood sugar spike from glycemic load when curve data isn't available.
     * GL is a better predictor than GI because it accounts for portion size.
     */
    private function estimateSpikeFromGL(float $gl, float $gi): float
    {
        // GL-based estimation: High GL (>20) = significant spike, Low GL (<10) = minimal
        return match (true) {
            $gl >= 30 => 50,
            $gl >= 20 => 35,
            $gl >= 15 => 20,
            $gl >= 10 => 10,
            $gl >= 5  => 0,
            default   => -10,
        };
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
            return ['success' => false, 'response' => ''];
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

    /**
     * Use AI to interpret a free-text meal description when keywords don't match.
     * Returns parsed meal impact or null on failure.
     */
    private function interpretMealWithAI(string $description): ?array
    {
        if (!AiSetting::getValue('simulation_ai_explanation', true)) {
            return null;
        }

        $systemPrompt = <<<'PROMPT'
You are a metabolic analysis engine. Given a meal description, return ONLY a JSON object with:
- "eating_habits": a short description of the diet pattern (e.g., "high carb meal", "balanced protein-rich meal")
- "sugar_impact": estimated blood sugar change in mg/dL (positive = increase, negative = decrease), integer between -30 and +50
- "cravings": one of "frequent", "occasional", "rare"
Return ONLY valid JSON, no explanation.
PROMPT;

        $result = $this->bedrock->ask($systemPrompt, "Meal description: {$description}", [
            'max_tokens' => 150,
            'temperature' => 0.1,
        ]);

        if (!$result['success']) {
            return null;
        }

        $parsed = json_decode($result['response'], true);
        if (!is_array($parsed)) {
            // Try extracting JSON from response
            if (preg_match('/\{.*\}/s', $result['response'], $matches)) {
                $parsed = json_decode($matches[0], true);
            }
        }

        return is_array($parsed) ? $parsed : null;
    }

    /**
     * Generate hormonal predictions from modified snapshot data.
     */
    private function generatePredictions(array $modifiedData): array
    {
        $predictions = [
            'cortisol' => $this->cortisolPrediction->predict($modifiedData),
        ];

        // PCOS-specific predictions
        if (isset($modifiedData['pcod']) || isset($modifiedData['pcos'])) {
            $predictions['cycle'] = $this->cyclePrediction->predict($modifiedData);
        }

        return $predictions;
    }
}
