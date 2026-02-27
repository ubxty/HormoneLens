<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHealthProfileRequest;
use App\Http\Requests\UpdateHealthProfileRequest;
use App\Http\Resources\HealthProfileResource;
use App\Repositories\HealthProfileRepository;
use Illuminate\Http\Request;

class HealthProfileController extends Controller
{
    public function __construct(
        private readonly HealthProfileRepository $profileRepo,
    ) {}

    public function store(StoreHealthProfileRequest $request)
    {
        $user = $request->user();

        if ($user->healthProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Health profile already exists. Use PUT to update.',
            ], 422);
        }

        $profile = $this->profileRepo->create(
            array_merge($request->validated(), ['user_id' => $user->id])
        );

        return response()->json([
            'success' => true,
            'message' => 'Health profile created successfully.',
            'data' => new HealthProfileResource($profile),
        ], 201);
    }

    public function show(Request $request)
    {
        $profile = $this->profileRepo->findByUser($request->user());

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Health profile not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new HealthProfileResource($profile),
        ]);
    }

    public function update(UpdateHealthProfileRequest $request)
    {
        $profile = $this->profileRepo->findByUser($request->user());

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Health profile not found. Please create one first.',
            ], 404);
        }

        $profile = $this->profileRepo->update($profile, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Health profile updated successfully.',
            'data' => new HealthProfileResource($profile),
        ]);
    }
}
