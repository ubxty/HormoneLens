<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function __construct(
        private readonly UserRepository $userRepo,
    ) {}

    /**
     * List all users (paginated).
     */
    public function index(Request $request)
    {
        $users = $this->userRepo->paginate(
            perPage: (int) $request->query('per_page', 20),
            search: $request->query('search'),
            isAdmin: $request->query('is_admin'),
        );

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
        ]);
    }

    /**
     * Show a single user with related data.
     */
    public function show(int $id)
    {
        $user = $this->userRepo->findById($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $user->load(['healthProfile', 'diseaseDiabetes', 'diseasePcod', 'activeDigitalTwin', 'simulations' => fn ($q) => $q->latest()->limit(10)]);

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Toggle admin status for a user.
     */
    public function toggleAdmin(int $id)
    {
        $user = $this->userRepo->findById($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $user->update(['is_admin' => !$user->is_admin]);

        return response()->json([
            'success' => true,
            'message' => $user->is_admin ? 'User promoted to admin.' : 'Admin privilege revoked.',
            'data' => new UserResource($user->fresh()),
        ]);
    }
}
