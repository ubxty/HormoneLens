<?php

namespace App\Repositories;

use App\Models\Simulation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HistoryRepository
{
    public function paginateByUser(User $user, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Simulation::where('user_id', $user->id)->with('alerts');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function findByIdForUser(int $id, User $user): ?Simulation
    {
        return Simulation::where('user_id', $user->id)
            ->with(['digitalTwin', 'alerts'])
            ->find($id);
    }
}
