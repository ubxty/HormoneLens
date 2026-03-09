<?php

namespace App\Http\Controllers;

use App\Services\DigitalTwin\DigitalTwinService;
use App\Services\Prediction\AndrogenPredictionService;
use App\Services\Prediction\CortisolPredictionService;
use App\Services\Prediction\CyclePredictionService;
use App\Services\Prediction\HbA1cPredictionService;
use App\Services\Prediction\LongTermProjectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function __construct(
        private readonly DigitalTwinService $twinService,
        private readonly CortisolPredictionService $cortisol,
        private readonly AndrogenPredictionService $androgen,
        private readonly CyclePredictionService $cycle,
        private readonly HbA1cPredictionService $hba1c,
        private readonly LongTermProjectionService $longTerm,
    ) {}

    /**
     * Get all predictions for the authenticated user's active digital twin.
     */
    public function all(Request $request): JsonResponse
    {
        $twin = $this->twinService->getActive($request->user());
        if (!$twin) {
            return response()->json([
                'success' => false,
                'message' => 'No active Digital Twin found. Please generate one first.',
            ], 422);
        }

        $snapshot = $twin->snapshot_data;
        $userId = $request->user()->id;

        return response()->json([
            'success' => true,
            'data' => [
                'cortisol' => $this->cortisol->predict($snapshot),
                'androgen' => $this->androgen->predict($snapshot),
                'cycle' => $this->cycle->predict($snapshot),
                'hba1c' => $this->hba1c->predict($snapshot, $userId),
                'long_term' => $this->longTerm->project($snapshot, $userId),
            ],
        ]);
    }

    /**
     * Get cortisol prediction.
     */
    public function cortisol(Request $request): JsonResponse
    {
        $twin = $this->resolveActiveTwin($request);
        if ($twin instanceof JsonResponse) return $twin;

        $timeOfDay = $request->query('time_of_day');

        return response()->json([
            'success' => true,
            'data' => [
                'prediction' => $this->cortisol->predict($twin->snapshot_data, $timeOfDay),
                'daily_curve' => $this->cortisol->dailyCurve($twin->snapshot_data),
            ],
        ]);
    }

    /**
     * Get androgen prediction (PCOS-specific).
     */
    public function androgen(Request $request): JsonResponse
    {
        $twin = $this->resolveActiveTwin($request);
        if ($twin instanceof JsonResponse) return $twin;

        return response()->json([
            'success' => true,
            'data' => $this->androgen->predict($twin->snapshot_data),
        ]);
    }

    /**
     * Get cycle prediction (PCOS-specific).
     */
    public function cycle(Request $request): JsonResponse
    {
        $twin = $this->resolveActiveTwin($request);
        if ($twin instanceof JsonResponse) return $twin;

        return response()->json([
            'success' => true,
            'data' => $this->cycle->predict($twin->snapshot_data),
        ]);
    }

    /**
     * Get HbA1c prediction (Diabetes-specific).
     */
    public function hba1c(Request $request): JsonResponse
    {
        $twin = $this->resolveActiveTwin($request);
        if ($twin instanceof JsonResponse) return $twin;

        return response()->json([
            'success' => true,
            'data' => $this->hba1c->predict($twin->snapshot_data, $request->user()->id),
        ]);
    }

    /**
     * Get long-term outcome projections.
     */
    public function longTerm(Request $request): JsonResponse
    {
        $twin = $this->resolveActiveTwin($request);
        if ($twin instanceof JsonResponse) return $twin;

        return response()->json([
            'success' => true,
            'data' => $this->longTerm->project($twin->snapshot_data, $request->user()->id),
        ]);
    }

    private function resolveActiveTwin(Request $request)
    {
        $twin = $this->twinService->getActive($request->user());
        if (!$twin) {
            return response()->json([
                'success' => false,
                'message' => 'No active Digital Twin found. Please generate one first.',
            ], 422);
        }
        return $twin;
    }
}
