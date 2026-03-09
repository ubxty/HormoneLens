<?php

namespace App\Http\Controllers;

use App\Http\Requests\RunSimulationRequest;
use App\Http\Resources\SimulationResource;
use App\Repositories\SimulationRepository;
use App\Services\Simulation\SimulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Manage chained simulation sessions (AR3).
 * A session chains multiple simulations where each builds on the previous result.
 */
class SimulationSessionController extends Controller
{
    public function __construct(
        private readonly SimulationService $simulationService,
        private readonly SimulationRepository $simulationRepo,
    ) {}

    /**
     * Run a chained simulation that builds on a previous simulation's modified state.
     */
    public function chain(RunSimulationRequest $request): JsonResponse
    {
        $parentId = $request->input('parent_simulation_id');

        if (!$parentId) {
            return response()->json([
                'success' => false,
                'message' => 'parent_simulation_id is required for chained simulations.',
            ], 422);
        }

        $parentSim = $this->simulationRepo->findById((int) $parentId);
        if (!$parentSim || $parentSim->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Parent simulation not found.',
            ], 404);
        }

        // Run simulation using parent's modified snapshot as the base
        $simulation = $this->simulationService->simulateFromSnapshot(
            $request->user(),
            $request->validated(),
            $parentSim->modified_twin_data,
            $parentSim->simulated_risk_score,
            $parentSim->id,
        );

        $simulation->load('alerts');

        return response()->json([
            'success' => true,
            'message' => 'Chained simulation completed.',
            'data' => new SimulationResource($simulation),
        ], 201);
    }

    /**
     * Get all simulations in a chain starting from a root simulation.
     */
    public function chain_history(Request $request, int $rootId): JsonResponse
    {
        $rootSim = $this->simulationRepo->findById($rootId);
        if (!$rootSim || $rootSim->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Simulation not found.',
            ], 404);
        }

        // Walk the chain forward from root (max 50 to prevent runaway loops)
        $chain = collect([$rootSim]);
        $currentId = $rootSim->id;

        for ($i = 0; $i < 50; $i++) {
            $child = \App\Models\Simulation::where('user_id', $request->user()->id)
                ->where('input_data->parent_simulation_id', $currentId)
                ->first();

            if (!$child) break;
            $chain->push($child);
            $currentId = $child->id;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'chain_length' => $chain->count(),
                'risk_trajectory' => $chain->map(fn ($s) => [
                    'id' => $s->id,
                    'type' => $s->type->value,
                    'risk_score' => $s->simulated_risk_score,
                    'risk_change' => $s->risk_change,
                    'risk_category' => $s->risk_category_after->value,
                ])->values(),
                'simulations' => SimulationResource::collection($chain),
            ],
        ]);
    }
}
