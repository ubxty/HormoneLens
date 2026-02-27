<?php

namespace App\Http\Controllers;

use App\Http\Requests\FoodImpactRequest;
use App\Http\Resources\SimulationResource;
use App\Services\Simulation\SimulationService;
use Illuminate\Http\Request;

class FoodImpactController extends Controller
{
    public function __construct(
        private readonly SimulationService $simulationService,
    ) {}

    /**
     * Simulate food impact (glycemic + alternatives).
     */
    public function __invoke(FoodImpactRequest $request)
    {
        $result = $this->simulationService->simulateFoodImpact(
            $request->user(),
            $request->validated(),
        );

        return response()->json([
            'success' => true,
            'message' => 'Food impact simulation completed.',
            'data' => $result,
        ], 201);
    }
}
