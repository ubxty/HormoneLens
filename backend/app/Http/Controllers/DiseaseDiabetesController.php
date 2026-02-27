<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiabetesRequest;
use App\Http\Resources\DiabetesResource;
use App\Repositories\DiabetesRepository;
use Illuminate\Http\Request;

class DiseaseDiabetesController extends Controller
{
    public function __construct(
        private readonly DiabetesRepository $diabetesRepo,
    ) {}

    public function store(StoreDiabetesRequest $request)
    {
        $user = $request->user();

        $diabetes = $this->diabetesRepo->createOrUpdate($user, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Diabetes data saved successfully.',
            'data' => new DiabetesResource($diabetes),
        ], 201);
    }

    public function show(Request $request)
    {
        $diabetes = $this->diabetesRepo->findByUser($request->user());

        if (!$diabetes) {
            return response()->json([
                'success' => false,
                'message' => 'Diabetes data not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new DiabetesResource($diabetes),
        ]);
    }
}
