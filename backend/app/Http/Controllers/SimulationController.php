<?php

namespace App\Http\Controllers;

use App\Http\Requests\RunSimulationRequest;
use App\Http\Resources\SimulationResource;
use App\Repositories\SimulationRepository;
use App\Services\Simulation\SimulationService;
use Illuminate\Http\Request;

class SimulationController extends Controller
{
    public function __construct(
        private readonly SimulationService $simulationService,
        private readonly SimulationRepository $simulationRepo,
    ) {}

    /**
     * Run a lifestyle simulation (meal / sleep / stress).
     */
    public function run(RunSimulationRequest $request)
    {
        $simulation = $this->simulationService->simulateLifestyleChange(
            $request->user(),
            $request->validated(),
        );

        $simulation->load('alerts');

        return response()->json([
            'success' => true,
            'message' => 'Simulation completed.',
            'data' => new SimulationResource($simulation),
        ], 201);
    }

    /**
     * List authenticated user's simulations (paginated).
     */
    public function index(Request $request)
    {
        $simulations = $this->simulationRepo->paginateByUser(
            $request->user(),
            perPage: (int) $request->query('per_page', 15),
        );

        return response()->json([
            'success' => true,
            'data' => SimulationResource::collection($simulations),
            'meta' => [
                'current_page' => $simulations->currentPage(),
                'last_page' => $simulations->lastPage(),
                'per_page' => $simulations->perPage(),
                'total' => $simulations->total(),
            ],
        ]);
    }

    /**
     * Show a single simulation (must belong to auth user).
     */
    public function show(Request $request, int $id)
    {
        $simulation = $this->simulationRepo->findById($id);

        if (!$simulation || $simulation->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Simulation not found.',
            ], 404);
        }

        $simulation->load('alerts');

        return response()->json([
            'success' => true,
            'data' => new SimulationResource($simulation),
        ]);
    }
}
