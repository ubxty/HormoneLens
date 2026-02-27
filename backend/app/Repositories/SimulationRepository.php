<?php

namespace App\Repositories;

use App\Models\Simulation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SimulationRepository
{
    public function create(array $data): Simulation
    {
        return Simulation::create($data);
    }

    public function findById(int $id): ?Simulation
    {
        return Simulation::with(['digitalTwin', 'alerts'])->find($id);
    }

    public function paginateByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Simulation::where('user_id', $user->id)
            ->with('alerts')
            ->latest()
            ->paginate($perPage);
    }

    public function paginateAll(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Simulation::with(['user', 'digitalTwin']);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['risk_category_after'])) {
            $query->where('risk_category_after', $filters['risk_category_after']);
        }
        if (!empty($filters['date_from'] ?? $filters['from_date'] ?? null)) {
            $query->where('created_at', '>=', $filters['date_from'] ?? $filters['from_date']);
        }
        if (!empty($filters['date_to'] ?? $filters['to_date'] ?? null)) {
            $query->where('created_at', '<=', $filters['date_to'] ?? $filters['to_date']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function countByUser(User $user): int
    {
        return Simulation::where('user_id', $user->id)->count();
    }

    public function totalCount(): int
    {
        return Simulation::count();
    }

    public function todayCount(): int
    {
        return Simulation::whereDate('created_at', today())->count();
    }

    public function weekCount(): int
    {
        return Simulation::where('created_at', '>=', now()->subWeek())->count();
    }

    public function highRiskCountForUser(User $user, int $days = 7): int
    {
        return Simulation::where('user_id', $user->id)
            ->whereIn('risk_category_after', ['high', 'critical'])
            ->where('created_at', '>=', now()->subDays($days))
            ->count();
    }

    public function dailyCountForPeriod(int $days = 30)
    {
        return Simulation::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->pluck('count', 'date');
    }
}
