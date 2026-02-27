<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ReportResource;
use App\Repositories\AlertRepository;
use App\Repositories\DigitalTwinRepository;
use App\Repositories\SimulationRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private readonly UserRepository $userRepo,
        private readonly SimulationRepository $simRepo,
        private readonly AlertRepository $alertRepo,
        private readonly DigitalTwinRepository $twinRepo,
    ) {}

    /**
     * Generate an aggregate report for a given period.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'period_days' => 'sometimes|integer|min:1|max:365',
        ]);

        $days = (int) $request->query('period_days', 30);
        $from = now()->subDays($days);

        $data = [
            'period_days' => $days,
            'period_start' => $from->toDateString(),
            'period_end' => now()->toDateString(),
            'new_users' => $this->userRepo->newUsersCount($days),
            'total_simulations' => $this->simRepo->totalCount(),
            'simulations_in_period' => $this->simRepo->weekCount(),
            'risk_distribution' => $this->twinRepo->riskDistribution(),
            'average_risk_score' => round($this->twinRepo->averageRiskScore(), 2),
            'daily_risk_scores' => $this->twinRepo->dailyRiskScoresForPeriod($days),
            'daily_simulations' => $this->simRepo->dailyCountForPeriod($days),
            'daily_alerts_by_severity' => $this->alertRepo->dailySeverityCountForPeriod($days),
        ];

        return response()->json([
            'success' => true,
            'data' => new ReportResource($data),
        ]);
    }
}
