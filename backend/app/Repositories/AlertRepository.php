<?php

namespace App\Repositories;

use App\Models\Alert;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AlertRepository
{
    public function create(array $data): Alert
    {
        return Alert::create($data);
    }

    public function findById(int $id): ?Alert
    {
        return Alert::find($id);
    }

    public function paginateByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Alert::where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    public function markAsRead(Alert $alert): Alert
    {
        $alert->update(['is_read' => true]);
        return $alert;
    }

    public function unreadCountForUser(User $user): int
    {
        return Alert::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    public function paginateAll(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Alert::with('user');

        if (!empty($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (isset($filters['is_read'])) {
            $query->where('is_read', $filters['is_read']);
        }
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function totalUnreadCount(): int
    {
        return Alert::where('is_read', false)->count();
    }

    public function dailySeverityCountForPeriod(int $days = 30)
    {
        return Alert::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, severity, COUNT(*) as count')
            ->groupByRaw('DATE(created_at), severity')
            ->orderBy('date')
            ->get();
    }
}
