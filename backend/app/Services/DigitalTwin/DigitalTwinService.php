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
        $user->load(['healthProfile', 'diseaseData.disease']);

        $hp = $user->healthProfile;
        if (!$hp) {
            throw new \RuntimeException('Health profile is required to generate a Digital Twin.');
        }

        // Build disease data map keyed by slug: ['diabetes' => [...fields...], 'pcod' => [...], ...]
        $diseaseDataMap = $user->allDiseaseDataKeyed();

        // Calculate all scores
        $metabolic = $this->riskEngine->calculateMetabolicRisk($hp, $diseaseDataMap);
        $insulin = $this->riskEngine->calculateInsulinResistance($hp, $diseaseDataMap);
        $hormonal = $this->riskEngine->calculateHormonalImbalance($hp, $diseaseDataMap);
        $overall = $this->riskEngine->calculateOverallRisk($metabolic, $insulin, $hormonal);
        $sleepScore = $this->riskEngine->calculateSleepScore((float) $hp->avg_sleep_hours);
        $stressVal = is_object($hp->stress_level) ? $hp->stress_level->value : ($hp->stress_level ?? 'medium');
        $stressScore = $this->riskEngine->calculateStressScore($stressVal);
        $dietScore = $this->riskEngine->calculateDietScore($hp, $diseaseDataMap);
        $riskCategory = $this->riskEngine->categorizeRisk($overall);

        // Build snapshot: health_profile + each disease slug as key
        $snapshot = ['health_profile' => $hp->toArray()];
        foreach ($diseaseDataMap as $slug => $fieldValues) {
            $snapshot[$slug] = $fieldValues;
        }

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
