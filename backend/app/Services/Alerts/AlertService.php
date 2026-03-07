<?php

namespace App\Services\Alerts;

use App\Enums\AlertType;
use App\Enums\Severity;
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

        // Check risk threshold
        if (($simulationResult['simulated_risk_score'] ?? 0) > 75) {
            $alerts->push($this->createAlert($user, $simulationId, AlertType::RISK_THRESHOLD, Severity::CRITICAL,
                'High Risk Score Detected',
                'Your simulated risk score of ' . round($simulationResult['simulated_risk_score'], 1) . ' exceeded the safe threshold of 75. Consider lifestyle modifications.'
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

        // Check stress
        $stressLevel = $inputData['parameters']['stress_level'] ?? $inputData['type'] ?? null;
        if ($stressLevel === 'high' || ($simulationResult['type'] ?? '') === 'stress') {
            $modifiedData = $simulationResult['modified_twin_data'] ?? [];
            if (($modifiedData['health_profile']['stress_level'] ?? '') === 'high') {
                $alerts->push($this->createAlert($user, $simulationId, AlertType::HIGH_STRESS, Severity::WARNING,
                    'High Stress Level',
                    'High stress elevates cortisol, worsening insulin resistance. Consider stress management techniques.'
                ));
            }
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

        return $this->alertRepo->create([
            'user_id' => $user->id,
            'simulation_id' => $simulationId,
            'type' => $type->value,
            'title' => $title,
            'message' => $enhanced,
            'severity' => $severity->value,
            'is_read' => false,
        ]);
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
        $highGiFoods = [
            'white rice', 'sugar', 'candy', 'soda', 'cola', 'white bread',
            'potato', 'fries', 'chips', 'pastry', 'cake', 'cookie',
            'sweet', 'jalebi', 'gulab jamun', 'rasgulla', 'ladoo',
            'maida', 'naan', 'pizza', 'burger',
        ];

        $foodItem = strtolower($inputData['food_item'] ?? $inputData['description'] ?? '');

        foreach ($highGiFoods as $food) {
            if (str_contains($foodItem, $food)) {
                return true;
            }
        }

        // Check RAG explanation for high glycemic indicators
        $lowerExplanation = strtolower($ragExplanation);
        return str_contains($lowerExplanation, 'high glycemic')
            || str_contains($lowerExplanation, 'blood sugar spike')
            || str_contains($lowerExplanation, 'glucose spike');
    }
}
