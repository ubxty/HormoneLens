<?php

namespace App\Http\Controllers;

use App\Http\Requests\FoodCompareRequest;
use App\Models\Simulation;
use App\Services\Simulation\GlucoseCurveService;
use App\Services\DigitalTwin\DigitalTwinService;
use Illuminate\Http\JsonResponse;

class FoodCompareController extends Controller
{
    public function __construct(
        private readonly GlucoseCurveService $glucoseCurve,
        private readonly DigitalTwinService $twinService,
    ) {}

    public function __invoke(FoodCompareRequest $request): JsonResponse
    {
        $twin = $this->twinService->getActive($request->user());
        if (!$twin) {
            return response()->json([
                'success' => false,
                'message' => 'No active Digital Twin found. Please generate one first.',
            ], 422);
        }

        $snapshot = $twin->snapshot_data;
        $validated = $request->validated();

        // Support temporal comparison: same food at different times, or different foods at different times
        $mealTimeA = $validated['meal_time_a'] ?? $validated['meal_time'] ?? null;
        $mealTimeB = $validated['meal_time_b'] ?? $validated['meal_time'] ?? null;
        $quantityA = $validated['quantity_a'] ?? null;
        $quantityB = $validated['quantity_b'] ?? null;

        $curveA = $this->glucoseCurve->predict($validated['food_a'], $snapshot, $mealTimeA, $quantityA);
        $curveB = $this->glucoseCurve->predict($validated['food_b'], $snapshot, $mealTimeB, $quantityB);

        $spikeA = $curveA['peak']['glucose_mg_dl'];
        $spikeB = $curveB['peak']['glucose_mg_dl'];

        $comparisonData = [
            'food_a' => $curveA,
            'food_b' => $curveB,
            'comparison' => [
                'spike_difference' => round($spikeA - $spikeB, 1),
                'peak_time_difference' => $curveA['peak']['time_minutes'] - $curveB['peak']['time_minutes'],
                'recovery_difference' => $curveA['recovery_minutes'] - $curveB['recovery_minutes'],
                'gl_difference' => round(
                    ($curveA['food']['glycemic_load_adjusted'] ?? $curveA['food']['glycemic_load'])
                    - ($curveB['food']['glycemic_load_adjusted'] ?? $curveB['food']['glycemic_load']),
                    1
                ),
                'better_choice' => $spikeA <= $spikeB
                    ? $curveA['food']['name']
                    : $curveB['food']['name'],
                'is_temporal_comparison' => $mealTimeA !== $mealTimeB,
            ],
        ];

        // Store comparison as a simulation record (B10)
        Simulation::create([
            'user_id' => $request->user()->id,
            'digital_twin_id' => $twin->id,
            'type' => 'food_impact',
            'input_data' => [
                'food_a' => $validated['food_a'],
                'food_b' => $validated['food_b'],
                'meal_time_a' => $mealTimeA,
                'meal_time_b' => $mealTimeB,
                'quantity_a' => $quantityA,
                'quantity_b' => $quantityB,
                'comparison_type' => 'food_compare',
            ],
            'modified_twin_data' => $snapshot,
            'original_risk_score' => $twin->overall_risk_score,
            'simulated_risk_score' => $twin->overall_risk_score,
            'risk_change' => 0,
            'risk_category_before' => $twin->risk_category->value,
            'risk_category_after' => $twin->risk_category->value,
            'rag_explanation' => null,
            'rag_confidence' => 0,
            'results' => $comparisonData,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Food comparison complete.',
            'data' => $comparisonData,
        ]);
    }
}
