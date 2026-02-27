<?php

namespace App\Http\Controllers;

use App\Http\Resources\AlertResource;
use App\Repositories\AlertRepository;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function __construct(
        private readonly AlertRepository $alertRepo,
    ) {}

    /**
     * List alerts for the authenticated user (paginated).
     */
    public function index(Request $request)
    {
        $alerts = $this->alertRepo->paginateByUser(
            $request->user(),
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
     * Mark a single alert as read.
     */
    public function markRead(Request $request, int $id)
    {
        $alert = $this->alertRepo->findById($id);

        if (!$alert || $alert->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Alert not found.',
            ], 404);
        }

        $this->alertRepo->markAsRead($alert);

        return response()->json([
            'success' => true,
            'message' => 'Alert marked as read.',
        ]);
    }

    /**
     * Get the count of unread alerts.
     */
    public function unreadCount(Request $request)
    {
        $count = $this->alertRepo->unreadCountForUser($request->user());

        return response()->json([
            'success' => true,
            'data' => ['count' => $count],
        ]);
    }
}
