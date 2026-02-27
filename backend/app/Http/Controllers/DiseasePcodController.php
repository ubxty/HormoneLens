<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePcodRequest;
use App\Http\Resources\PcodResource;
use App\Repositories\PcodRepository;
use Illuminate\Http\Request;

class DiseasePcodController extends Controller
{
    public function __construct(
        private readonly PcodRepository $pcodRepo,
    ) {}

    public function store(StorePcodRequest $request)
    {
        $user = $request->user();

        $pcod = $this->pcodRepo->createOrUpdate($user, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'PCOD data saved successfully.',
            'data' => new PcodResource($pcod),
        ], 201);
    }

    public function show(Request $request)
    {
        $pcod = $this->pcodRepo->findByUser($request->user());

        if (!$pcod) {
            return response()->json([
                'success' => false,
                'message' => 'PCOD data not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new PcodResource($pcod),
        ]);
    }
}
