<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertResource;
use App\Repositories\AlertRepository;
use Illuminate\Http\Request;

class AlertManagementController extends Controller
{
    public function __construct(
        private readonly AlertRepository $alertRepo,
    ) {}

    /**
     * List all alerts across users (paginated, filterable).
     */
    public function index(Request $request)
    {
        $filters = $request->only(['severity', 'type', 'user_id', 'date_from', 'date_to']);

        $alerts = $this->alertRepo->paginateAll(
            filters: $filters,
            perPage: (int) $request->query('per_page', 20),
        );

        return response()->json([
            'success' => true,
            'data' => AlertResource::collection($alerts),
            'meta' => [
                'current_page' => $alerts->currentPage(),
                'last_page' => $alerts->lastPage(),
                'per_page' => $alerts->perPage(),
                'total' => $alerts->total(),
            ],
        ]);
    }

    /**
     * Show a single alert.
     */
    public function show(int $id)
    {
        $alert = $this->alertRepo->findById($id);

        if (!$alert) {
            return response()->json([
                'success' => false,
                'message' => 'Alert not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new AlertResource($alert),
        ]);
    }
}
