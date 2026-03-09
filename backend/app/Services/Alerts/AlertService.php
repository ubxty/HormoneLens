<?php

namespace App\Services\Alerts;

use App\Enums\AlertType;
use App\Enums\Severity;
use App\Events\AlertCreated;
use App\Models\AiSetting;
use App\Models\Alert;
use App\Models\User;
use App\Repositories\AlertRepository;
use App\Repositories\SimulationRepository;
use App\Services\AI\BedrockService;
use App\Services\AI\PromptTemplates;
use Illuminate\Support\Collection;

class AlertService
{
    public function __construct(
        private readonly AlertRepository $alertRepo,
        private readonly SimulationRepository $simulationRepo,
        private readonly BedrockService $bedrock,
    ) {}

    /**
     * Evaluate simulation results and generate appropriate alerts.
     */
    public function evaluate(User $user, array $simulationResult, ?int $simulationId = null): Collection
    {
        $alerts = collect();

        // Get adaptive threshold for this user (AL2)
        $riskThreshold = $this->getAdaptiveRiskThreshold($user);

        // Check risk threshold
        if (($simulationResult['simulated_risk_score'] ?? 0) > $riskThreshold) {
            $alerts->push($this->createAlert($user, $simulationId, AlertType::RISK_THRESHOLD, Severity::CRITICAL,
                'High Risk Score Detected',
                'Your simulated risk score of ' . round($simulationResult['simulated_risk_score'], 1) . ' exceeded the threshold of ' . round($riskThreshold, 1) . '. Consider lifestyle modifications.'
            ));
        }

        // Check for high glycemic food (from input data or RAG)
        $inputData = $simulationResult['input_data'] ?? [];
        if ($this->isHighGlycemicFood($inputData, $simulationResult['rag_explanation'] ?? '')) {
            $alerts->push($this->createAlert($user, $simulationId, AlertType::HIGH_GI, Severity::WARNING,
                'High Glycemic Food Detected',
                'High glycemic food may cause a spike in your blood sugar level. Consider healthier alternatives.'
            ));
        }

        // Check sleep
        $sleepHours = $inputData['parameters']['sleep_hours'] ?? null;
        if ($sleepHours !== null && $sleepHours < 6) {
            $alerts->push($this->createAlert($user, $simulationId, AlertType::LOW_SLEEP, Severity::WARNING,
                'Insufficient Sleep',
                'Sleep below 6 hours increases cortisol and metabolic risk. Aim for 7-9 hours of quality sleep.'
            ));
        }

        // Check stress from modified snapshot directly
        $modifiedData = $simulationResult['modified_twin_data'] ?? [];
        if (($modifiedData['health_profile']['stress_level'] ?? '') === 'high') {
            $alerts->push($this->createAlert($user, $simulationId, AlertType::HIGH_STRESS, Severity::WARNING,
                'High Stress Level',
                'High stress elevates cortisol, worsening insulin resistance. Consider stress management techniques.'
            ));
        }

        // Check repeated high risk (3+ in 7 days)
        $highRiskCount = $this->simulationRepo->highRiskCountForUser($user, 7);
        if ($highRiskCount >= 3) {
            $alerts->push($this->createAlert($user, $simulationId, AlertType::REPEATED_RISK, Severity::CRITICAL,
                'Repeated High Risk Pattern',
                "You've had {$highRiskCount} high-risk simulations in the past 7 days. Please review your lifestyle habits and consider consulting a healthcare provider."
            ));
        }

        return $alerts;
    }

    private function createAlert(
        User $user,
        ?int $simulationId,
        AlertType $type,
        Severity $severity,
        string $title,
        string $message
    ): Alert {
        $enhanced = $this->enhanceAlertMessage($type, $severity, $title, $message);

        $alert = $this->alertRepo->create([
            'user_id' => $user->id,
            'simulation_id' => $simulationId,
            'type' => $type->value,
            'title' => $title,
            'message' => $enhanced,
            'severity' => $severity->value,
            'is_read' => false,
        ]);

        // Broadcast alert for real-time push (AL1)
        AlertCreated::dispatch($alert);

        return $alert;
    }

    /**
     * Enhance alert message with AI-generated contextual advice using fast model.
     */
    private function enhanceAlertMessage(AlertType $type, Severity $severity, string $title, string $message): string
    {
        if (!AiSetting::getValue('alert_ai_enhancement', true)) {
            return $message;
        }

        $systemPrompt = PromptTemplates::alertContext();
        $userMessage = "Alert Type: {$type->value}"
            . "\nSeverity: {$severity->value}"
            . "\nTitle: {$title}"
            . "\nOriginal Message: {$message}"
            . "\n\nEnhance the message with a brief, actionable recommendation (max 2 sentences). Keep the original meaning.";

        $result = $this->bedrock->ask($systemPrompt, $userMessage, [
            'model' => \Ubxty\BedrockAi\Facades\Bedrock::resolveAlias('fast'),
        ]);

        return $result['success'] ? $result['response'] : $message;
    }

    private function isHighGlycemicFood(array $inputData, string $ragExplanation): bool
    {
        $foodItem = strtolower($inputData['food_item'] ?? $inputData['description'] ?? '');
        if (empty($foodItem)) {
            return false;
        }

        // Check against food_glycemic_data database table
        $food = \App\Models\FoodGlycemicData::findByName($foodItem);
        if ($food && $food->glycemic_index >= 60) {
            return true;
        }

        // Check RAG explanation for high glycemic indicators
        $lowerExplanation = strtolower($ragExplanation);
        return str_contains($lowerExplanation, 'high glycemic')
            || str_contains($lowerExplanation, 'blood sugar spike')
            || str_contains($lowerExplanation, 'glucose spike');
    }

    /**
     * Calculate adaptive risk threshold for a user (AL2).
     * Users with consistently lower risk get tighter thresholds (more sensitive alerts),
     * while users with chronic high risk get slightly relaxed thresholds to avoid alert fatigue.
     */
    private function getAdaptiveRiskThreshold(User $user): float
    {
        $baseThreshold = 75.0;

        $recentScores = \App\Models\Simulation::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(14))
            ->pluck('simulated_risk_score');

        if ($recentScores->count() < 3) {
            return $baseThreshold;
        }

        $avgScore = $recentScores->avg();

        // If user typically has low risk, make threshold more sensitive
        // If user typically has high risk, relax slightly to avoid fatigue
        return match (true) {
            $avgScore < 30 => 65.0,   // Healthy user — alert earlier
            $avgScore < 50 => 70.0,   // Moderate — slightly sensitive
            $avgScore > 80 => 80.0,   // Chronic high risk — reduce alert fatigue
            default => $baseThreshold,
        };
    }
}
