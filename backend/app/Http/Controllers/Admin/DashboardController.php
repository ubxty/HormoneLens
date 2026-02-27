<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\DashboardSummaryResource;
use App\Repositories\AlertRepository;
use App\Repositories\DigitalTwinRepository;
use App\Repositories\SimulationRepository;
use App\Repositories\UserRepository;

class DashboardController extends Controller
{
    public function __construct(
        private readonly UserRepository $userRepo,
        private readonly SimulationRepository $simRepo,
        private readonly AlertRepository $alertRepo,
        private readonly DigitalTwinRepository $twinRepo,
    ) {}

    public function __invoke()
    {
        $data = [
            'total_users' => $this->userRepo->totalCount(),
            'new_users_7d' => $this->userRepo->newUsersCount(days: 7),
            'simulations_total' => $this->simRepo->totalCount(),
            'simulations_today' => $this->simRepo->todayCount(),
            'simulations_week' => $this->simRepo->weekCount(),
            'avg_risk_score' => $this->twinRepo->averageRiskScore(),
            'risk_distribution' => $this->twinRepo->riskDistribution(),
            'unread_alerts' => $this->alertRepo->totalUnreadCount(),
        ];

        return response()->json([
            'success' => true,
            'data' => new DashboardSummaryResource($data),
        ]);
    }
}
