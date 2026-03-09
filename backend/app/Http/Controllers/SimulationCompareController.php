<?php

namespace App\Http\Controllers;

use App\Http\Resources\SimulationResource;
use App\Repositories\SimulationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Compare multiple simulations side-by-side (AR4).
 */
class SimulationCompareController extends Controller
{
    public function __construct(
        private readonly SimulationRepository $simulationRepo,
    ) {}

    /**
     * Compare 2-5 simulations by their IDs.
     * POST /api/simulations/compare { "simulation_ids": [1, 2, 3] }
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'simulation_ids' => ['required', 'array', 'min:2', 'max:5'],
            'simulation_ids.*' => ['required', 'integer'],
        ]);

        $userId = $request->user()->id;
        $simulations = collect();

        foreach ($request->input('simulation_ids') as $id) {
            $sim = $this->simulationRepo->findById((int) $id);
            if (!$sim || $sim->user_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'message' => "Simulation #{$id} not found or does not belong to you.",
                ], 404);
            }
            $simulations->push($sim);
        }

        // Build comparison matrix
        $comparison = $simulations->map(function ($sim) {
            $results = $sim->results ?? [];
            $scores = $results['scores'] ?? [];

            return [
                'id' => $sim->id,
                'type' => $sim->type->value,
                'description' => $sim->input_data['description'] ?? '',
                'original_risk' => (float) $sim->original_risk_score,
                'simulated_risk' => (float) $sim->simulated_risk_score,
                'risk_change' => (float) $sim->risk_change,
                'risk_category' => $sim->risk_category_after->value,
                'scores' => [
                    'metabolic' => $scores['metabolic_health_score'] ?? null,
                    'insulin' => $scores['insulin_resistance_score'] ?? null,
                    'sleep' => $scores['sleep_score'] ?? null,
                    'stress' => $scores['stress_score'] ?? null,
                    'diet' => $scores['diet_score'] ?? null,
                ],
                'predictions' => $results['predictions'] ?? null,
                'created_at' => $sim->created_at->toISOString(),
            ];
        });

        // Calculate summary statistics
        $riskChanges = $simulations->pluck('risk_change')->map(fn ($v) => (float) $v);
        $bestSim = $simulations->sortBy('risk_change')->first();
        $worstSim = $simulations->sortByDesc('risk_change')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'simulations' => $comparison,
                'summary' => [
                    'best_scenario' => [
                        'id' => $bestSim->id,
                        'type' => $bestSim->type->value,
                        'risk_change' => (float) $bestSim->risk_change,
                    ],
                    'worst_scenario' => [
                        'id' => $worstSim->id,
                        'type' => $worstSim->type->value,
                        'risk_change' => (float) $worstSim->risk_change,
                    ],
                    'avg_risk_change' => round($riskChanges->avg(), 2),
                    'risk_range' => [
                        'min' => round($riskChanges->min(), 2),
                        'max' => round($riskChanges->max(), 2),
                    ],
                ],
            ],
        ]);
    }
}
