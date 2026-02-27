<?php

namespace App\Http\Controllers;

use App\Http\Resources\DigitalTwinResource;
use App\Services\DigitalTwin\DigitalTwinService;
use Illuminate\Http\Request;

class DigitalTwinController extends Controller
{
    public function __construct(
        private readonly DigitalTwinService $twinService,
    ) {}

    /**
     * Generate (or regenerate) the user's digital twin.
     */
    public function generate(Request $request)
    {
        $twin = $this->twinService->generate($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Digital twin generated successfully.',
            'data' => new DigitalTwinResource($twin),
        ], 201);
    }

    /**
     * Get the currently-active digital twin.
     */
    public function active(Request $request)
    {
        $twin = $this->twinService->getActive($request->user());

        if (!$twin) {
            return response()->json([
                'success' => false,
                'message' => 'No active digital twin found. Please generate one first.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new DigitalTwinResource($twin),
        ]);
    }

    /**
     * List all digital twin snapshots.
     */
    public function index(Request $request)
    {
        $twins = $this->twinService->getAllForUser($request->user());

        return response()->json([
            'success' => true,
            'data' => DigitalTwinResource::collection($twins),
        ]);
    }

    /**
     * Show a specific twin by ID (must belong to the auth user).
     */
    public function show(Request $request, int $id)
    {
        $twin = $this->twinService->findById($id);

        if (!$twin || $twin->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Digital twin not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new DigitalTwinResource($twin),
        ]);
    }
}
