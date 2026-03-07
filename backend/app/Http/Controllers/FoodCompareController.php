<?php

namespace App\Http\Controllers;

use App\Http\Requests\FoodCompareRequest;
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
        $mealTime = $request->validated('meal_time');

        $curveA = $this->glucoseCurve->predict($request->validated('food_a'), $snapshot, $mealTime);
        $curveB = $this->glucoseCurve->predict($request->validated('food_b'), $snapshot, $mealTime);

        return response()->json([
            'success' => true,
            'message' => 'Food comparison complete.',
            'data' => [
                'food_a' => $curveA,
                'food_b' => $curveB,
                'comparison' => [
                    'spike_difference' => round($curveA['peak']['glucose_mg_dl'] - $curveB['peak']['glucose_mg_dl'], 1),
                    'peak_time_difference' => $curveA['peak']['time_minutes'] - $curveB['peak']['time_minutes'],
                    'recovery_difference' => $curveA['recovery_minutes'] - $curveB['recovery_minutes'],
                    'better_choice' => $curveA['peak']['glucose_mg_dl'] <= $curveB['peak']['glucose_mg_dl']
                        ? $curveA['food']['name']
                        : $curveB['food']['name'],
                ],
            ],
        ]);
    }
}
