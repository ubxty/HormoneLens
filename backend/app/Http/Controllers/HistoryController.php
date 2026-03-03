<?php

namespace App\Http\Controllers;

use App\Http\Resources\HistoryResource;
use App\Http\Resources\SimulationResource;
use App\Repositories\HistoryRepository;
use App\Repositories\SimulationRepository;
use App\Services\Simulation\SimulationService;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function __construct(
        private readonly HistoryRepository $historyRepo,
        private readonly SimulationRepository $simulationRepo,
        private readonly SimulationService $simulationService,
    ) {}

    /**
     * List simulation history (paginated, filterable by type/date).
     */
    public function index(Request $request)
    {
        $filters = $request->only(['type', 'date_from', 'date_to']);

        $history = $this->historyRepo->paginateByUser(
            $request->user(),
            filters: $filters,
            perPage: (int) $request->query('per_page', 15),
        );

        return response()->json([
            'success' => true,
            'data' => HistoryResource::collection($history),
            'meta' => [
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
                'per_page' => $history->perPage(),
                'total' => $history->total(),
            ],
        ]);
    }

    /**
     * Show a single history entry.
     */
    public function show(Request $request, int $id)
    {
        $entry = $this->historyRepo->findByIdForUser($id, $request->user());

        if (!$entry) {
            return response()->json([
                'success' => false,
                'message' => 'History entry not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new HistoryResource($entry),
        ]);
    }

    /**
     * Re-run a previous simulation with the same parameters.
     */
    public function rerun(Request $request, int $id)
    {
        $original = $this->historyRepo->findByIdForUser($id, $request->user());

        if (!$original) {
            return response()->json([
                'success' => false,
                'message' => 'History entry not found.',
            ], 404);
        }

        $inputData = $original->input_data;
        $inputData['type'] = $original->type->value;

        $simulation = $this->simulationService->simulateLifestyleChange(
            $request->user(),
            $inputData,
        );

        $simulation->load('alerts');

        return response()->json([
            'success' => true,
            'message' => 'Simulation re-run completed.',
            'data' => new SimulationResource($simulation),
        ], 201);
    }

    /**
     * Delete a history entry.
     */
    public function destroy(Request $request, int $id)
    {
        $deleted = $this->historyRepo->deleteForUser($id, $request->user());

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'History entry not found or could not be deleted.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'History entry deleted successfully.',
        ]);
    }
}
