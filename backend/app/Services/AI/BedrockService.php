<?php

namespace App\Services\AI;

use Ubxty\BedrockAi\Exceptions\BedrockException;
use Ubxty\BedrockAi\Exceptions\CostLimitExceededException;
use Ubxty\BedrockAi\Exceptions\RateLimitException;
use Ubxty\BedrockAi\Facades\Bedrock;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BedrockService
{
    public function __construct(
        private readonly GuardrailService $guardrails,
    ) {}

    /**
     * Send a prompt to Bedrock and return the response.
     * Uses the Converse API for model-agnostic compatibility (Nova, Claude, Titan, etc.).
     * Falls back to 'fast' alias if the primary model fails.
     */
    public function ask(string $systemPrompt, string $userMessage, array $options = []): array
    {
        $userMessage = $this->guardrails->sanitizeInput($userMessage);

        $model = $options['model'] ?? Bedrock::resolveAlias('default');
        $maxTokens = $options['max_tokens'] ?? 1024;
        $temperature = $options['temperature'] ?? 0.3;

        try {
            $result = $this->converseHttp($model, $systemPrompt, $userMessage, $maxTokens, $temperature);

            $response = $this->guardrails->validateResponse($result['response']);

            return [
                'response'      => $response,
                'input_tokens'  => $result['input_tokens'],
                'output_tokens' => $result['output_tokens'],
                'cost'          => $result['cost'] ?? 0,
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
     * Stream a response using the Converse API, calling $onChunk for each text fragment.
     */
    public function stream(string $systemPrompt, string $userMessage, callable $onChunk, array $options = []): array
    {
        $userMessage = $this->guardrails->sanitizeInput($userMessage);

        $model = $options['model'] ?? Bedrock::resolveAlias('default');
        $maxTokens = $options['max_tokens'] ?? 1024;
        $temperature = $options['temperature'] ?? 0.7;

        // For ABSK tokens, streaming via converseStream won't work with SDK.
        // Fall back to non-streaming call and deliver the full response as a single chunk.
        if ($this->isAbskMode()) {
            $result = $this->converseHttp($model, $systemPrompt, $userMessage, $maxTokens, $temperature);
            $onChunk($result['response'], ['type' => 'delta']);
            return $result;
        }

        $messages = [['role' => 'user', 'content' => $userMessage]];

        return Bedrock::streamingClient()->converseStream(
            modelId: $model,
            messages: $messages,
            onChunk: $onChunk,
            systemPrompt: $systemPrompt,
            maxTokens: $maxTokens,
            temperature: $temperature,
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

            $result = $this->converseHttp($fallbackModel, $systemPrompt, $userMessage, $maxTokens, $temperature);

            $response = $this->guardrails->validateResponse($result['response']);

            return [
                'response'      => $response,
                'input_tokens'  => $result['input_tokens'],
                'output_tokens' => $result['output_tokens'],
                'cost'          => $result['cost'] ?? 0,
                'model_used'    => $result['model_id'],
                'latency_ms'    => $result['latency_ms'],
                'success'       => true,
            ];
        } catch (\Throwable $e) {
            Log::error('Bedrock fallback also failed', ['error' => $e->getMessage()]);
            return $this->errorResult('AI service is currently unavailable.');
        }
    }

    /**
     * Call the Converse API via HTTP with Bearer auth (for ABSK tokens)
     * or via the package's SDK-based converse method for standard IAM credentials.
     */
    private function converseHttp(string $modelId, string $systemPrompt, string $userMessage, int $maxTokens, float $temperature): array
    {
        $startTime = microtime(true);
        $modelId = Bedrock::resolveAlias($modelId);

        if ($this->isAbskMode()) {
            $key = config('bedrock.connections.default.keys.0');
            $region = $key['region'] ?? 'us-east-1';
            $bearerToken = str_starts_with($key['aws_key'] ?? '', 'ABSK') ? $key['aws_key'] : $key['aws_secret'];
            $url = "https://bedrock-runtime.{$region}.amazonaws.com/model/{$modelId}/converse";

            $body = [
                'messages' => [
                    ['role' => 'user', 'content' => [['text' => $userMessage]]],
                ],
                'inferenceConfig' => ['maxTokens' => $maxTokens, 'temperature' => $temperature],
            ];

            if ($systemPrompt !== '') {
                $body['system'] = [['text' => $systemPrompt]];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $bearerToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(60)->post($url, $body);

            if ($response->status() === 429) {
                throw new RateLimitException('429 Too many requests - rate limited', 429);
            }

            if (! $response->successful()) {
                throw new BedrockException("Bedrock Converse HTTP Error: {$response->status()} - {$response->body()}", $response->status());
            }

            $data = $response->json();
            $inputTokens = $data['usage']['inputTokens'] ?? 0;
            $outputTokens = $data['usage']['outputTokens'] ?? 0;

            return [
                'response' => $data['output']['message']['content'][0]['text'] ?? '',
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'cost' => 0,
                'latency_ms' => (int) ((microtime(true) - $startTime) * 1000),
                'model_id' => $modelId,
            ];
        }

        // Standard IAM credentials — use the package's Converse API
        $messages = [['role' => 'user', 'content' => $userMessage]];

        return Bedrock::converse(
            modelId: $modelId,
            messages: $messages,
            systemPrompt: $systemPrompt,
            maxTokens: $maxTokens,
            temperature: $temperature,
        );
    }

    private function isAbskMode(): bool
    {
        $key = config('bedrock.connections.default.keys.0', []);

        return str_starts_with($key['aws_key'] ?? '', 'ABSK')
            || str_starts_with($key['aws_secret'] ?? '', 'ABSK');
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
