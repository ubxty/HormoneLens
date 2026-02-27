<?php

namespace App\Services\DigitalTwin;

use App\Models\DigitalTwin;
use App\Models\User;
use App\Repositories\DigitalTwinRepository;
use App\Services\Risk\RiskEngineService;

class DigitalTwinService
{
    public function __construct(
        private readonly RiskEngineService $riskEngine,
        private readonly DigitalTwinRepository $repository,
    ) {}

    /**
     * Generate a new Digital Twin for a user based on current health profile + disease data.
     */
    public function generate(User $user): DigitalTwin
    {
        $user->load(['healthProfile', 'diseaseDiabetes', 'diseasePcod']);

        $hp = $user->healthProfile;
        if (!$hp) {
            throw new \RuntimeException('Health profile is required to generate a Digital Twin.');
        }

        $diabetes = $user->diseaseDiabetes;
        $pcod = $user->diseasePcod;

        // Calculate all scores
        $metabolic = $this->riskEngine->calculateMetabolicRisk($hp, $diabetes, $pcod);
        $insulin = $this->riskEngine->calculateInsulinResistance($hp, $diabetes, $pcod);
        $hormonal = $this->riskEngine->calculateHormonalImbalance($hp, $diabetes, $pcod);
        $overall = $this->riskEngine->calculateOverallRisk($metabolic, $insulin, $hormonal);
        $sleepScore = $this->riskEngine->calculateSleepScore((float) $hp->avg_sleep_hours);
        $stressScore = $this->riskEngine->calculateStressScore($hp->stress_level->value);
        $dietScore = $this->riskEngine->calculateDietScore($hp, $diabetes, $pcod);
        $riskCategory = $this->riskEngine->categorizeRisk($overall);

        // Build snapshot
        $snapshot = [
            'health_profile' => $hp->toArray(),
            'diabetes' => $diabetes?->toArray(),
            'pcod' => $pcod?->toArray(),
        ];

        // Deactivate previous twins
        $this->repository->deactivateAll($user);

        // Create new active twin
        return $this->repository->create([
            'user_id' => $user->id,
            'metabolic_health_score' => round($metabolic, 2),
            'insulin_resistance_score' => round($insulin, 2),
            'sleep_score' => round($sleepScore, 2),
            'stress_score' => round($stressScore, 2),
            'diet_score' => round($dietScore, 2),
            'overall_risk_score' => round($overall, 2),
            'risk_category' => $riskCategory->value,
            'snapshot_data' => $snapshot,
            'is_active' => true,
        ]);
    }

    /**
     * Get the active Digital Twin for a user.
     */
    public function getActive(User $user): ?DigitalTwin
    {
        return $this->repository->findActiveByUser($user);
    }

    /**
     * Get all Digital Twins for a user.
     */
    public function getAllForUser(User $user)
    {
        return $this->repository->getByUser($user);
    }

    /**
     * Find a specific twin by ID.
     */
    public function findById(int $id): ?DigitalTwin
    {
        return $this->repository->findById($id);
    }
}
