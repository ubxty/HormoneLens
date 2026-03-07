<?php

namespace App\Services\AI;

use Ubxty\BedrockAi\Exceptions\BedrockException;
use Ubxty\BedrockAi\Exceptions\CostLimitExceededException;
use Ubxty\BedrockAi\Exceptions\RateLimitException;
use Ubxty\BedrockAi\Facades\Bedrock;
use Illuminate\Support\Facades\Log;

class BedrockService
{
    public function __construct(
        private readonly GuardrailService $guardrails,
    ) {}

    /**
     * Send a prompt to Bedrock and return the response.
     * Falls back to 'fast' alias if the primary model fails.
     */
    public function ask(string $systemPrompt, string $userMessage, array $options = []): array
    {
        $userMessage = $this->guardrails->sanitizeInput($userMessage);

        $model = $options['model'] ?? Bedrock::resolveAlias('default');
        $maxTokens = $options['max_tokens'] ?? 1024;
        $temperature = $options['temperature'] ?? 0.3;

        try {
            $result = Bedrock::invoke(
                modelId: $model,
                systemPrompt: $systemPrompt,
                userMessage: $userMessage,
                maxTokens: $maxTokens,
                temperature: $temperature,
                pricing: true,
            );

            $response = $this->guardrails->validateResponse($result['response']);

            return [
                'response'      => $response,
                'input_tokens'  => $result['input_tokens'],
                'output_tokens' => $result['output_tokens'],
                'cost'          => $result['cost'],
                'model_used'    => $result['model_id'],
                'latency_ms'    => $result['latency_ms'],
                'success'       => true,
            ];
        } catch (RateLimitException $e) {
            Log::warning('Bedrock rate limited, trying fast model', ['model' => $model]);
            return $this->fallbackAsk($systemPrompt, $userMessage, $maxTokens, $temperature);
        } catch (CostLimitExceededException $e) {
            Log::error('Bedrock cost limit exceeded', [
                'limit_type' => $e->getLimitType(),
                'limit'      => $e->getLimit(),
            ]);
            return $this->errorResult('AI service temporarily unavailable due to usage limits.');
        } catch (BedrockException $e) {
            Log::error('Bedrock error', ['message' => $e->getMessage()]);
            return $this->errorResult('AI service is currently unavailable.');
        }
    }

    /**
     * Stream a response, calling $onChunk for each text fragment.
     */
    public function stream(string $systemPrompt, string $userMessage, callable $onChunk, array $options = []): array
    {
        $userMessage = $this->guardrails->sanitizeInput($userMessage);

        $model = $options['model'] ?? Bedrock::resolveAlias('default');
        $maxTokens = $options['max_tokens'] ?? 1024;

        return Bedrock::stream(
            modelId: $model,
            systemPrompt: $systemPrompt,
            userMessage: $userMessage,
            maxTokens: $maxTokens,
            onChunk: $onChunk,
        );
    }

    /**
     * Build a multi-turn conversation.
     */
    public function conversation(?string $model = null): \Ubxty\BedrockAi\Conversation\ConversationBuilder
    {
        return Bedrock::conversation($model);
    }

    /**
     * Check if Bedrock is configured (lightweight, no API call).
     */
    public function isConfigured(): bool
    {
        return Bedrock::isConfigured();
    }

    /**
     * Check if Bedrock is configured and reachable.
     */
    public function isAvailable(): bool
    {
        return Bedrock::isConfigured() && ($this->testConnection()['success'] ?? false);
    }

    public function testConnection(): array
    {
        return Bedrock::testConnection();
    }

    public function listModels(): array
    {
        return Bedrock::fetchModels();
    }

    public function getUsage(int $days = 30): array
    {
        return Bedrock::usage()->getAggregatedUsage($days);
    }

    public function getPricing(): array
    {
        return Bedrock::pricing()->getPricing();
    }

    // ── Private ──────────────────────────────────────

    private function fallbackAsk(string $systemPrompt, string $userMessage, int $maxTokens, float $temperature): array
    {
        try {
            $fallbackModel = Bedrock::resolveAlias('fast');
            $result = Bedrock::invoke(
                modelId: $fallbackModel,
                systemPrompt: $systemPrompt,
                userMessage: $userMessage,
                maxTokens: $maxTokens,
                temperature: $temperature,
                pricing: true,
            );

            $response = $this->guardrails->validateResponse($result['response']);

            return [
                'response'      => $response,
                'input_tokens'  => $result['input_tokens'],
                'output_tokens' => $result['output_tokens'],
                'cost'          => $result['cost'],
                'model_used'    => $result['model_id'],
                'latency_ms'    => $result['latency_ms'],
                'success'       => true,
            ];
        } catch (\Throwable $e) {
            Log::error('Bedrock fallback also failed', ['error' => $e->getMessage()]);
            return $this->errorResult('AI service is currently unavailable.');
        }
    }

    private function errorResult(string $message): array
    {
        return [
            'response'      => $message,
            'input_tokens'  => 0,
            'output_tokens' => 0,
            'cost'          => 0,
            'model_used'    => null,
            'latency_ms'    => 0,
            'success'       => false,
        ];
    }
}
