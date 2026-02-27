<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SimulationResource;
use App\Repositories\SimulationRepository;
use Illuminate\Http\Request;

class SimulationLogController extends Controller
{
    public function __construct(
        private readonly SimulationRepository $simRepo,
    ) {}

    /**
     * List all simulations across users (paginated, filterable).
     */
    public function index(Request $request)
    {
        $filters = $request->only(['type', 'user_id', 'date_from', 'date_to', 'search']);

        $simulations = $this->simRepo->paginateAll(
            filters: $filters,
            perPage: (int) $request->query('per_page', 20),
        );

        return response()->json([
            'success' => true,
            'data' => SimulationResource::collection($simulations),
            'meta' => [
                'current_page' => $simulations->currentPage(),
                'last_page' => $simulations->lastPage(),
                'per_page' => $simulations->perPage(),
                'total' => $simulations->total(),
                'from' => $simulations->firstItem(),
                'to' => $simulations->lastItem(),
            ],
        ]);
    }

    /**
     * Show a single simulation.
     */
    public function show(int $id)
    {
        $simulation = $this->simRepo->findById($id);

        if (!$simulation) {
            return response()->json([
                'success' => false,
                'message' => 'Simulation not found.',
            ], 404);
        }

        $simulation->load(['user', 'alerts']);

        return response()->json([
            'success' => true,
            'data' => new SimulationResource($simulation),
        ]);
    }
}
