# HormoneLens — Amazon Bedrock Integration Plan

> **Package**: `ubxty/bedrock-ai` (v0.0.1)
> **Target Models**: Claude 3.5 Sonnet, Claude 3 Haiku (fallback), Amazon Nova (optional)
> **Scope**: Full LLM integration — RAG answer synthesis, simulation explanations, food analysis, alerts, admin management UI

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [Phase 0 — Package Installation & Configuration](#2-phase-0--package-installation--configuration)
3. [Phase 1 — Core AI Service Layer](#3-phase-1--core-ai-service-layer)
4. [Phase 2 — RAG LLM Enhancement](#4-phase-2--rag-llm-enhancement)
5. [Phase 3 — Simulation & Alert AI Enhancement](#5-phase-3--simulation--alert-ai-enhancement)
6. [Phase 4 — Admin Bedrock Management UI](#6-phase-4--admin-bedrock-management-ui)
7. [Phase 5 — Streaming & Frontend Integration](#7-phase-5--streaming--frontend-integration)
8. [Phase 6 — Guardrails & Safety](#8-phase-6--guardrails--safety)
9. [Database Migrations](#9-database-migrations)
10. [System Prompts](#10-system-prompts)
11. [File-by-File Change Map](#11-file-by-file-change-map)
12. [Testing Strategy](#12-testing-strategy)
13. [Environment Variables](#13-environment-variables)

---

## 1. Architecture Overview

### Current State (Rule-Based)
```
User Query → Tokenize → Tree Traversal → Keyword Match → Concatenate Pages → Return Raw Text
```

### Target State (LLM-Enhanced)
```
User Query → Tokenize → Tree Traversal → Keyword Match → Retrieve Pages
                                                              ↓
                                                     Bedrock (Claude 3.5)
                                                              ↓
                                              Synthesized, Contextual Answer
```

### Integration Points (5 Services)

| Service | Current Behavior | After Bedrock |
|---|---|---|
| `RagAnswerBuilder` | Concatenates page text, truncates at 2000 chars | Sends retrieved pages + user question to Claude for synthesis |
| `SimulationService` | Stores raw RAG text as `rag_explanation` | Gets LLM-generated explanation of simulation impact |
| `AlertService` | Hardcoded alert message templates | LLM-generated contextual alert messages |
| `RiskEngineService` | Pure math scoring, no explanations | AI-generated risk factor narratives (post-calculation) |
| `FoodImpactController` | Static food alternatives list | LLM-generated food analysis with personalized alternatives |

### Package Integration Flow
```
App Service → BedrockService (wrapper) → ubxty/bedrock-ai (Facade/DI)
                                              ↓
                                     BedrockManager::invoke()
                                              ↓
                                     AWS Bedrock Converse API
                                              ↓
                                     Claude 3.5 Sonnet Response
```

---

## 2. Phase 0 — Package Installation & Configuration

### 2.1 Install Package

```bash
composer require ubxty/bedrock-ai
php artisan vendor:publish --tag=bedrock-config
```

This creates `config/bedrock.php` with the default configuration.

### 2.2 Environment Variables to Add

Add to `.env` and `.env.example`:

```env
# ── Bedrock AI ────────────────────────────
BEDROCK_AWS_KEY="${AWS_ACCESS_KEY_ID}"
BEDROCK_AWS_SECRET="${AWS_SECRET_ACCESS_KEY}"
BEDROCK_REGION=us-east-1

# Cost limits
BEDROCK_DAILY_LIMIT=10.00
BEDROCK_MONTHLY_LIMIT=100.00

# Logging
BEDROCK_LOGGING_ENABLED=true
BEDROCK_LOG_CHANNEL=stack

# Optional: additional keys for rotation
# BEDROCK_AWS_KEY_2=
# BEDROCK_AWS_SECRET_2=
# BEDROCK_LABEL_2=backup-key
```

### 2.3 Configure `config/bedrock.php`

After publishing, update the config to define model aliases and connections:

```php
// In config/bedrock.php — key sections to configure:

'aliases' => [
    'smart'   => 'anthropic.claude-3-5-sonnet-20241022-v2:0',
    'fast'    => 'anthropic.claude-3-haiku-20240307-v1:0',
    'default' => 'anthropic.claude-3-5-sonnet-20241022-v2:0',
],

'default_max_tokens' => 1024,

'cost_limits' => [
    'daily'   => env('BEDROCK_DAILY_LIMIT', 10.00),
    'monthly' => env('BEDROCK_MONTHLY_LIMIT', 100.00),
],
```

### 2.4 Register Service Provider

In `bootstrap/providers.php`, ensure `Ubxty\BedrockAi\BedrockServiceProvider` is auto-discovered (the package has auto-discovery, so no manual registration unless `dont-discover` is set).

### 2.5 Verify Installation

```bash
php artisan bedrock:configure --show    # Verify config
php artisan bedrock:test                # Test connection
php artisan bedrock:models --provider=anthropic  # List available models
```

---

## 3. Phase 1 — Core AI Service Layer

### 3.1 New File: `app/Services/AI/BedrockService.php`

This is the **single wrapper** around `ubxty/bedrock-ai` that all other services call. It handles model selection, system prompts, error handling, and fallback logic.

```php
<?php

namespace App\Services\AI;

use Ubxty\BedrockAi\Facades\Bedrock;
use Ubxty\BedrockAi\Exceptions\BedrockException;
use Ubxty\BedrockAi\Exceptions\RateLimitException;
use Ubxty\BedrockAi\Exceptions\CostLimitExceededException;
use Illuminate\Support\Facades\Log;

class BedrockService
{
    /**
     * Send a prompt to Bedrock and return the response text.
     * Falls back to 'fast' model if 'smart' fails.
     */
    public function ask(string $systemPrompt, string $userMessage, array $options = []): array
    {
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

            return [
                'response'     => $result['response'],
                'input_tokens' => $result['input_tokens'],
                'output_tokens'=> $result['output_tokens'],
                'cost'         => $result['cost'],
                'model_used'   => $result['model_id'],
                'latency_ms'   => $result['latency_ms'],
                'success'      => true,
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
     * Stream a response, calling $onChunk for each partial response.
     */
    public function stream(string $systemPrompt, string $userMessage, callable $onChunk, array $options = []): array
    {
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
     * Check if Bedrock is configured and reachable.
     */
    public function isAvailable(): bool
    {
        return Bedrock::isConfigured() && ($this->testConnection()['success'] ?? false);
    }

    /**
     * Test the connection.
     */
    public function testConnection(): array
    {
        return Bedrock::testConnection();
    }

    /**
     * Get available models.
     */
    public function listModels(): array
    {
        return Bedrock::fetchModels();
    }

    /**
     * Get usage statistics.
     */
    public function getUsage(int $days = 30): array
    {
        return Bedrock::usage()->getAggregatedUsage($days);
    }

    /**
     * Get pricing information.
     */
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

            return [
                'response'     => $result['response'],
                'input_tokens' => $result['input_tokens'],
                'output_tokens'=> $result['output_tokens'],
                'cost'         => $result['cost'],
                'model_used'   => $result['model_id'],
                'latency_ms'   => $result['latency_ms'],
                'success'      => true,
            ];
        } catch (\Throwable $e) {
            Log::error('Bedrock fallback also failed', ['error' => $e->getMessage()]);
            return $this->errorResult('AI service is currently unavailable.');
        }
    }

    private function errorResult(string $message): array
    {
        return [
            'response'     => $message,
            'input_tokens' => 0,
            'output_tokens'=> 0,
            'cost'         => 0,
            'model_used'   => null,
            'latency_ms'   => 0,
            'success'      => false,
        ];
    }
}
```

### 3.2 New File: `app/Services/AI/PromptTemplates.php`

Centralized prompt templates used across all integration points.

```php
<?php

namespace App\Services\AI;

class PromptTemplates
{
    public static function ragSynthesis(): string
    {
        return <<<'PROMPT'
You are HormoneLens AI, a medical knowledge assistant specializing in hormonal health,
metabolic disorders, PCOD, diabetes, and insulin resistance.

RULES:
- Synthesize the provided knowledge base excerpts into a clear, actionable answer.
- Use simple language a patient can understand.
- Include specific recommendations when relevant.
- If the excerpts don't contain enough information, say so honestly.
- Never diagnose. Always recommend consulting a healthcare provider for medical decisions.
- Keep responses under 300 words.
- Format with short paragraphs, no markdown headers.
PROMPT;
    }

    public static function simulationExplanation(): string
    {
        return <<<'PROMPT'
You are HormoneLens AI, explaining the results of a metabolic simulation.

RULES:
- Explain how the simulated lifestyle change affects the user's hormonal and metabolic health.
- Reference the specific risk score changes provided.
- Give 2-3 actionable recommendations.
- Use empathetic, encouraging tone.
- Keep response under 200 words.
- Never diagnose or prescribe medication.
PROMPT;
    }

    public static function foodImpact(): string
    {
        return <<<'PROMPT'
You are HormoneLens AI, a nutrition advisor for hormonal and metabolic health.

RULES:
- Analyze the food item's impact on blood sugar, insulin, and hormonal balance.
- Consider the user's specific condition (diabetes, PCOD, etc.).
- Provide glycemic index context.
- Suggest 3-5 healthier alternatives with brief explanations.
- Use simple, practical language.
- Keep response under 250 words.
- Never replace professional dietary advice.
PROMPT;
    }

    public static function alertContext(): string
    {
        return <<<'PROMPT'
You are HormoneLens AI, generating a health alert message.

RULES:
- Write a concise, actionable alert message (2-3 sentences).
- Explain WHY this alert was triggered in the context of hormonal/metabolic health.
- Include one specific action the user can take.
- Use a caring but factual tone.
- Do not cause panic.
PROMPT;
    }

    public static function riskNarrative(): string
    {
        return <<<'PROMPT'
You are HormoneLens AI, providing a risk analysis narrative.

RULES:
- Summarize the user's risk profile based on the scores provided.
- Explain the top 2-3 contributing factors.
- Suggest lifestyle modifications that could improve their scores.
- Keep response under 200 words.
- Use encouraging tone, acknowledge positive aspects.
- Never diagnose.
PROMPT;
    }
}
```

### 3.3 Register in `AppServiceProvider` or `RagServiceProvider`

Add to `app/Providers/RagServiceProvider.php`:

```php
use App\Services\AI\BedrockService;

public function register(): void
{
    $this->app->bind(RagSearchInterface::class, RagSearchService::class);
    $this->app->bind(RagTraversalInterface::class, RagTraversalEngine::class);
    $this->app->singleton(BedrockService::class);
}
```

---

## 4. Phase 2 — RAG LLM Enhancement

### 4.1 Modify: `app/Services/Rag/RagAnswerBuilder.php`

**Current**: Concatenates page text, truncates at 2000 chars.
**After**: Sends retrieved pages to Claude for synthesis.

```php
<?php

namespace App\Services\Rag;

use App\Models\RagPage;
use App\Repositories\Rag\RagPageRepository;
use App\Services\AI\BedrockService;
use App\Services\AI\PromptTemplates;

class RagAnswerBuilder
{
    public function __construct(
        private readonly RagPageRepository $pageRepo,
        private readonly BedrockService $bedrock,
    ) {}

    public function build(array $terminalNodes, array $path, string $question = ''): array
    {
        $nodeIds = collect($terminalNodes)->pluck('id')->toArray();
        $pages = $this->pageRepo->getByNodeIds($nodeIds);

        if ($pages->isEmpty()) {
            return [
                'answer'       => 'No relevant information found in the knowledge base.',
                'source_pages' => [],
                'ai_metadata'  => null,
            ];
        }

        // Collect raw page content as context
        $context = $pages->map(fn(RagPage $p) => $p->content)->implode("\n\n---\n\n");

        // Build reasoning path string
        $pathStr = collect($path)->pluck('title')->implode(' → ');

        // Attempt LLM synthesis
        $aiResult = $this->synthesizeWithAI($question, $context, $pathStr);

        $sourcePages = $pages->map(fn(RagPage $p) => [
            'id'          => $p->id,
            'page_number' => $p->page_number,
            'content'     => $p->content,
        ])->values()->toArray();

        return [
            'answer'       => $aiResult['response'],
            'source_pages' => $sourcePages,
            'ai_metadata'  => $aiResult['success'] ? [
                'model'    => $aiResult['model_used'],
                'tokens'   => $aiResult['input_tokens'] + $aiResult['output_tokens'],
                'cost'     => $aiResult['cost'],
                'latency'  => $aiResult['latency_ms'],
            ] : null,
        ];
    }

    private function synthesizeWithAI(string $question, string $context, string $path): array
    {
        $userMessage = "KNOWLEDGE BASE EXCERPTS:\n{$context}\n\n"
                     . "REASONING PATH: {$path}\n\n"
                     . "USER QUESTION: {$question}\n\n"
                     . "Synthesize a clear answer from the knowledge base excerpts above.";

        $result = $this->bedrock->ask(
            systemPrompt: PromptTemplates::ragSynthesis(),
            userMessage: $userMessage,
            options: ['max_tokens' => 512],
        );

        // If AI fails, fall back to concatenated text
        if (!$result['success']) {
            $truncated = mb_substr($context, 0, 2000);
            $result['response'] = $truncated . (mb_strlen($context) > 2000 ? '...' : '');
        }

        return $result;
    }
}
```

### 4.2 Modify: `app/Services/Rag/RagSearchService.php`

Pass the original question through to `RagAnswerBuilder::build()`:

```php
// In the search() method, change the answerBuilder call:

// BEFORE:
$answerData = $this->answerBuilder->build(
    $traversalResult['terminal_nodes'],
    $traversalResult['path']
);

// AFTER:
$answerData = $this->answerBuilder->build(
    $traversalResult['terminal_nodes'],
    $traversalResult['path'],
    $question  // Pass original question for LLM context
);

// Also add ai_metadata to the return array:
return [
    'answer'         => $answerData['answer'],
    'reasoning_path' => $reasoningPath,
    'source_nodes'   => $sourceNodes,
    'source_pages'   => $answerData['source_pages'],
    'confidence'     => $confidenceScore,
    'ai_metadata'    => $answerData['ai_metadata'] ?? null,
];
```

### 4.3 Modify: `app/Http/Controllers/RagController.php`

Include `ai_metadata` in the API response so the frontend can show model info:

```php
// In the response, add:
'ai_powered' => !empty($result['ai_metadata']),
'ai_metadata' => $result['ai_metadata'],
```

---

## 5. Phase 3 — Simulation & Alert AI Enhancement

### 5.1 Modify: `app/Services/Simulation/SimulationService.php`

After calculating the risk change, ask Claude to explain the impact:

```php
// Add BedrockService to constructor:
use App\Services\AI\BedrockService;
use App\Services\AI\PromptTemplates;

public function __construct(
    private readonly DigitalTwinService $twinService,
    private readonly RiskEngineService $riskEngine,
    private readonly AlertService $alertService,
    private readonly RagSearchInterface $ragSearch,
    private readonly SimulationRepository $simulationRepo,
    private readonly BedrockService $bedrock,  // ADD
) {}

// In simulateLifestyleChange(), AFTER risk calculation and RAG search:
// Replace the raw RAG answer with AI-enhanced explanation:

$aiExplanation = $this->generateAIExplanation($type, $input, $originalRisk, $simulatedRisk, $ragResult);

// Store simulation — use AI explanation:
$simulation = $this->simulationRepo->create([
    // ... existing fields ...
    'rag_explanation' => $aiExplanation['response'],
    'rag_confidence'  => $ragResult['confidence'],
    'results' => [
        'scores'         => $newScores,
        'reasoning_path' => $ragResult['reasoning_path'],
        'ai_metadata'    => $aiExplanation['success'] ? [
            'model'   => $aiExplanation['model_used'],
            'tokens'  => $aiExplanation['input_tokens'] + $aiExplanation['output_tokens'],
            'cost'    => $aiExplanation['cost'],
        ] : null,
    ],
]);
```

**New private method:**

```php
private function generateAIExplanation(SimulationType $type, array $input, float $originalRisk, float $simulatedRisk, array $ragResult): array
{
    $riskChange = round($simulatedRisk - $originalRisk, 2);
    $direction = $riskChange > 0 ? 'increased' : 'decreased';

    $userMessage = "SIMULATION TYPE: {$type->value}\n"
                 . "DESCRIPTION: " . ($input['description'] ?? $type->value) . "\n"
                 . "ORIGINAL RISK: {$originalRisk}\n"
                 . "SIMULATED RISK: {$simulatedRisk}\n"
                 . "RISK CHANGE: {$riskChange} ({$direction})\n"
                 . "KNOWLEDGE BASE CONTEXT: {$ragResult['answer']}\n\n"
                 . "Explain how this lifestyle change affects the user's metabolic and hormonal health.";

    $result = $this->bedrock->ask(
        systemPrompt: PromptTemplates::simulationExplanation(),
        userMessage: $userMessage,
        options: ['max_tokens' => 384],
    );

    // Fallback to RAG answer if AI fails
    if (!$result['success']) {
        $result['response'] = $ragResult['answer'];
    }

    return $result;
}
```

### 5.2 Enhance Food Impact with AI

In `simulateFoodImpact()`, add AI-powered food analysis:

```php
// After getting RAG result, enhance with AI:
$aiFoodAnalysis = $this->generateFoodAnalysis($foodItem, $diseaseContext, $ragResult);

// Use AI alternatives instead of static list:
$alternatives = $aiFoodAnalysis['success']
    ? $this->parseAlternatives($aiFoodAnalysis['response'])
    : $this->buildFoodAlternatives($foodItem);
```

**New private method:**

```php
private function generateFoodAnalysis(string $foodItem, ?string $diseaseContext, array $ragResult): array
{
    $userMessage = "FOOD ITEM: {$foodItem}\n"
                 . "USER CONDITION: " . ($diseaseContext ?? 'general metabolic health') . "\n"
                 . "KNOWLEDGE BASE: {$ragResult['answer']}\n\n"
                 . "Analyze this food's impact and suggest alternatives. "
                 . "Format alternatives as a numbered list at the end.";

    return $this->bedrock->ask(
        systemPrompt: PromptTemplates::foodImpact(),
        userMessage: $userMessage,
        options: ['max_tokens' => 512],
    );
}
```

### 5.3 Modify: `app/Services/Alerts/AlertService.php`

Add AI-contextual alert messages:

```php
use App\Services\AI\BedrockService;
use App\Services\AI\PromptTemplates;

public function __construct(
    private readonly AlertRepository $alertRepo,
    private readonly SimulationRepository $simulationRepo,
    private readonly BedrockService $bedrock,  // ADD
) {}

// Modify createAlert() to optionally enhance with AI:
private function createAlert(
    User $user,
    ?int $simulationId,
    AlertType $type,
    Severity $severity,
    string $title,
    string $message
): Alert {
    // Try to enhance message with AI context
    $enhancedMessage = $this->enhanceAlertMessage($type, $title, $message);

    return $this->alertRepo->create([
        'user_id'       => $user->id,
        'simulation_id' => $simulationId,
        'type'          => $type->value,
        'title'         => $title,
        'message'       => $enhancedMessage,
        'severity'      => $severity->value,
        'is_read'       => false,
    ]);
}

private function enhanceAlertMessage(AlertType $type, string $title, string $fallbackMessage): string
{
    $result = $this->bedrock->ask(
        systemPrompt: PromptTemplates::alertContext(),
        userMessage: "ALERT TYPE: {$type->value}\nTITLE: {$title}\nCONTEXT: {$fallbackMessage}\n\n"
                   . "Write a personalized, actionable alert message.",
        options: ['model' => Bedrock::resolveAlias('fast'), 'max_tokens' => 128],
    );

    return $result['success'] ? $result['response'] : $fallbackMessage;
}
```

> **Note**: Alert AI uses the `fast` model (Claude Haiku) to minimize latency/cost since alerts are generated frequently.

---

## 6. Phase 4 — Admin Bedrock Management UI

### 6.1 New Controller: `app/Http/Controllers/Admin/BedrockManagementController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AI\BedrockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ubxty\BedrockAi\Facades\Bedrock;

class BedrockManagementController extends Controller
{
    public function __construct(
        private readonly BedrockService $bedrock,
    ) {}

    /** GET /api/admin/bedrock/status — connection + config status */
    public function status(): JsonResponse
    {
        $isConfigured = Bedrock::isConfigured();
        $testResult = $isConfigured ? $this->bedrock->testConnection() : null;

        return response()->json([
            'configured' => $isConfigured,
            'connected'  => $testResult['success'] ?? false,
            'test_result'=> $testResult,
            'config'     => [
                'region'        => config('bedrock.region'),
                'daily_limit'   => config('bedrock.cost_limits.daily'),
                'monthly_limit' => config('bedrock.cost_limits.monthly'),
                'logging'       => config('bedrock.logging.enabled'),
            ],
        ]);
    }

    /** GET /api/admin/bedrock/models — list available models */
    public function models(): JsonResponse
    {
        $models = $this->bedrock->listModels();

        return response()->json([
            'models' => $models,
            'aliases' => config('bedrock.aliases', []),
        ]);
    }

    /** GET /api/admin/bedrock/usage — usage statistics */
    public function usage(Request $request): JsonResponse
    {
        $days = $request->integer('days', 30);
        $usage = $this->bedrock->getUsage($days);
        $trend = Bedrock::usage()->getDailyTrend($days);

        return response()->json([
            'aggregated' => $usage,
            'daily_trend' => $trend,
            'period_days' => $days,
        ]);
    }

    /** GET /api/admin/bedrock/pricing — pricing information */
    public function pricing(): JsonResponse
    {
        $pricing = $this->bedrock->getPricing();

        return response()->json([
            'pricing' => $pricing,
        ]);
    }

    /** POST /api/admin/bedrock/test — test a specific model */
    public function test(Request $request): JsonResponse
    {
        $request->validate([
            'model' => 'nullable|string',
            'prompt' => 'nullable|string|max:500',
        ]);

        $model = $request->input('model', Bedrock::resolveAlias('default'));
        $prompt = $request->input('prompt', 'Respond with "HormoneLens AI is operational." in exactly those words.');

        $result = $this->bedrock->ask(
            systemPrompt: 'You are a test assistant. Follow instructions exactly.',
            userMessage: $prompt,
            options: ['model' => $model, 'max_tokens' => 64],
        );

        return response()->json($result);
    }

    /** GET /api/admin/bedrock/logs — invocation logs */
    public function logs(Request $request): JsonResponse
    {
        $logger = Bedrock::getLogger();
        $logs = $logger->getRecentLogs($request->integer('limit', 50));

        return response()->json([
            'logs' => $logs,
        ]);
    }
}
```

### 6.2 New Service: `app/Http/Controllers/Admin/BedrockSettingsController.php`

Separate controller for admin settings (model activation, alias management):

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BedrockSettingsController extends Controller
{
    /** GET /api/admin/bedrock/settings — current AI settings */
    public function index(): JsonResponse
    {
        $settings = AiSetting::all()->keyBy('key');

        return response()->json(['settings' => $settings]);
    }

    /** PUT /api/admin/bedrock/settings — update AI settings */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
        ]);

        foreach ($request->input('settings') as $setting) {
            AiSetting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']],
            );
        }

        return response()->json(['message' => 'Settings updated']);
    }
}
```

### 6.3 New Model: `app/Models/AiSetting.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    protected $casts = [
        'value' => 'json',
    ];
}
```

### 6.4 Admin API Routes

Add to `routes/api.php` inside the `admin` middleware group:

```php
// ── Bedrock AI Management ────────────────
Route::prefix('bedrock')->group(function () {
    Route::get('/status',   [BedrockManagementController::class, 'status']);
    Route::get('/models',   [BedrockManagementController::class, 'models']);
    Route::get('/usage',    [BedrockManagementController::class, 'usage']);
    Route::get('/pricing',  [BedrockManagementController::class, 'pricing']);
    Route::post('/test',    [BedrockManagementController::class, 'test']);
    Route::get('/logs',     [BedrockManagementController::class, 'logs']);

    Route::get('/settings',  [BedrockSettingsController::class, 'index']);
    Route::put('/settings',  [BedrockSettingsController::class, 'update']);
});
```

### 6.5 Admin Web Routes

Add to `routes/web.php` inside the admin group:

```php
Route::get('/bedrock',         [AdminPageController::class, 'bedrock'])->name('bedrock');
Route::get('/bedrock/models',  [AdminPageController::class, 'bedrockModels'])->name('bedrock.models');
Route::get('/bedrock/usage',   [AdminPageController::class, 'bedrockUsage'])->name('bedrock.usage');
```

### 6.6 Add to Admin PageController

In `app/Http/Controllers/Web/Admin/PageController.php`, add:

```php
public function bedrock()
{
    return view('admin.bedrock.index');
}

public function bedrockModels()
{
    return view('admin.bedrock.models');
}

public function bedrockUsage()
{
    return view('admin.bedrock.usage');
}
```

### 6.7 Update Admin Sidebar Navigation

In `resources/views/layouts/admin.blade.php`, add a new nav section after Knowledge Base:

```html
<!-- AI Configuration -->
<div class="adm-nav-section" style="margin-top: 1.5rem;">
    <div class="adm-nav-section-title">AI ENGINE</div>
    <a href="{{ route('admin.bedrock') }}"
       class="adm-nav-link {{ request()->routeIs('admin.bedrock') && !request()->routeIs('admin.bedrock.*') ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="adm-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <span>AI Dashboard</span>
    </a>
    <a href="{{ route('admin.bedrock.models') }}"
       class="adm-nav-link {{ request()->routeIs('admin.bedrock.models') ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="adm-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        <span>Models</span>
    </a>
    <a href="{{ route('admin.bedrock.usage') }}"
       class="adm-nav-link {{ request()->routeIs('admin.bedrock.usage') ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="adm-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        <span>Usage & Costs</span>
    </a>
</div>
```

### 6.8 Admin Views

#### `resources/views/admin/bedrock/index.blade.php` — AI Dashboard

This is the main Bedrock management page. Uses the existing `adm-*` design system and AlpineJS pattern.

**Key sections:**
1. **Status Card** — Connection status (green/red indicator), region, configured keys
2. **Quick Test** — Model selector dropdown + prompt input + "Test" button → shows response
3. **Active Models** — Cards showing each model alias (smart/fast/default) with mapped model ID
4. **Cost Limits** — Current daily/monthly limits with progress bars showing spend
5. **Recent Invocations** — Table of last 20 invocations with model, tokens, cost, latency

**Alpine.js data structure:**
```javascript
{
    status: null,
    models: [],
    testResult: null,
    testing: false,
    testModel: '',
    testPrompt: 'Hello, are you operational?',
    logs: [],
    loading: true,

    async init() {
        await Promise.all([
            this.loadStatus(),
            this.loadLogs(),
        ]);
        this.loading = false;
    },

    async loadStatus() {
        const res = await api.get('/admin/bedrock/status');
        this.status = res.data;
    },

    async loadLogs() {
        const res = await api.get('/admin/bedrock/logs?limit=20');
        this.logs = res.data.logs;
    },

    async runTest() {
        this.testing = true;
        const res = await api.post('/admin/bedrock/test', {
            model: this.testModel || undefined,
            prompt: this.testPrompt,
        });
        this.testResult = res.data;
        this.testing = false;
    },
}
```

#### `resources/views/admin/bedrock/models.blade.php` — Model Explorer

**Key sections:**
1. **Available Models Table** — All Bedrock models with provider filter, search box
2. **Model Aliases** — Current alias mappings (smart, fast, default) — editable
3. **Pricing** — Per-model input/output token pricing in a sortable table

**Alpine.js pattern:**
```javascript
{
    models: [],
    pricing: [],
    aliases: {},
    filter: '',
    providerFilter: '',
    loading: true,

    async init() {
        const [modelsRes, pricingRes] = await Promise.all([
            api.get('/admin/bedrock/models'),
            api.get('/admin/bedrock/pricing'),
        ]);
        this.models = modelsRes.data.models;
        this.aliases = modelsRes.data.aliases;
        this.pricing = pricingRes.data.pricing;
        this.loading = false;
    },

    get filteredModels() {
        return this.models.filter(m =>
            (!this.filter || m.modelId.includes(this.filter) || m.modelName?.includes(this.filter)) &&
            (!this.providerFilter || m.providerName === this.providerFilter)
        );
    },

    get providers() {
        return [...new Set(this.models.map(m => m.providerName))].sort();
    },
}
```

#### `resources/views/admin/bedrock/usage.blade.php` — Usage & Cost Analytics

**Key sections:**
1. **Usage Summary Cards** — Total invocations, total tokens, total cost (30d)
2. **Daily Trend Chart** — Chart.js line chart of invocations/tokens/cost per day
3. **Per-Model Breakdown** — Table showing each model's invocation count, tokens, cost
4. **Period Selector** — 7d / 30d / 90d buttons

**Chart.js integration (same CDN already loaded in admin layout):**
```javascript
{
    usage: null,
    trend: [],
    days: 30,
    chart: null,
    loading: true,

    async init() {
        await this.loadUsage();
        this.loading = false;
    },

    async loadUsage() {
        const res = await api.get(`/admin/bedrock/usage?days=${this.days}`);
        this.usage = res.data.aggregated;
        this.trend = res.data.daily_trend;
        this.$nextTick(() => this.renderChart());
    },

    renderChart() {
        if (this.chart) this.chart.destroy();
        const ctx = document.getElementById('usageChart').getContext('2d');
        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.trend.map(d => d.date),
                datasets: [{
                    label: 'Invocations',
                    data: this.trend.map(d => d.invocations),
                    borderColor: '#a78bfa',
                    tension: 0.4,
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { color: '#e2e8f0' } } },
                scales: {
                    x: { ticks: { color: '#94a3b8' } },
                    y: { ticks: { color: '#94a3b8' } },
                },
            },
        });
    },

    async changePeriod(d) {
        this.days = d;
        this.loading = true;
        await this.loadUsage();
        this.loading = false;
    },
}
```

---

## 7. Phase 5 — Streaming & Frontend Integration

### 7.1 Streaming API Endpoint

For the RAG knowledge query page (`/knowledge`), add a streaming option so the user sees the AI response build word-by-word.

**New route** in `routes/api.php` (authenticated):

```php
Route::post('/rag/query-stream', [RagController::class, 'stream']);
```

**In `RagController`:**

```php
public function stream(Request $request)
{
    $request->validate(['question' => 'required|string|max:500']);

    $user = $request->user();
    $question = $request->input('question');
    $diseaseContext = $user->healthProfile?->disease_type;

    // Get RAG context (non-streaming part)
    $ragResult = $this->ragSearch->search($question, $diseaseContext);

    // Build context from RAG
    $context = $ragResult['answer'];
    $path = implode(' → ', $ragResult['reasoning_path']);

    $systemPrompt = PromptTemplates::ragSynthesis();
    $userMessage = "KNOWLEDGE BASE EXCERPTS:\n{$context}\n\n"
                 . "REASONING PATH: {$path}\n\n"
                 . "USER QUESTION: {$question}\n\n"
                 . "Synthesize a clear answer.";

    // Stream response using Server-Sent Events
    return response()->stream(function () use ($systemPrompt, $userMessage) {
        $bedrock = app(BedrockService::class);
        $bedrock->stream($systemPrompt, $userMessage, function ($chunk) {
            echo "data: " . json_encode(['chunk' => $chunk]) . "\n\n";
            ob_flush();
            flush();
        });
        echo "data: " . json_encode(['done' => true]) . "\n\n";
        ob_flush();
        flush();
    }, 200, [
        'Content-Type'  => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'Connection'    => 'keep-alive',
        'X-Accel-Buffering' => 'no',
    ]);
}
```

### 7.2 Frontend EventSource Integration

In the knowledge/RAG query Blade view, add streaming support:

```javascript
async askStreaming(question) {
    this.answer = '';
    this.streaming = true;

    const token = document.querySelector('meta[name="api-token"]')?.content;
    const response = await fetch('/api/rag/query-stream', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
            'Accept': 'text/event-stream',
        },
        body: JSON.stringify({ question }),
    });

    const reader = response.body.getReader();
    const decoder = new TextDecoder();

    while (true) {
        const { done, value } = await reader.read();
        if (done) break;

        const text = decoder.decode(value);
        const lines = text.split('\n').filter(l => l.startsWith('data: '));

        for (const line of lines) {
            const data = JSON.parse(line.slice(6));
            if (data.done) {
                this.streaming = false;
                return;
            }
            this.answer += data.chunk;
        }
    }
    this.streaming = false;
},
```

---

## 8. Phase 6 — Guardrails & Safety

### 8.1 Input Sanitization

Create `app/Services/AI/GuardrailService.php`:

```php
<?php

namespace App\Services\AI;

class GuardrailService
{
    private const BLOCKED_PATTERNS = [
        '/\b(prescri(?:be|ption)|diagnos(?:e|is)|medicat(?:e|ion))\b/i',
    ];

    private const MAX_INPUT_LENGTH = 2000;

    /**
     * Sanitize user input before sending to Bedrock.
     */
    public function sanitizeInput(string $input): string
    {
        // Trim to max length
        $input = mb_substr(trim($input), 0, self::MAX_INPUT_LENGTH);

        // Remove any potential prompt injection markers
        $input = preg_replace('/\b(SYSTEM|ASSISTANT|HUMAN):/i', '', $input);

        return $input;
    }

    /**
     * Validate AI response before returning to user.
     */
    public function validateResponse(string $response): string
    {
        // Add medical disclaimer if response contains health advice
        if (preg_match('/\b(recommend|should|try|consider|avoid)\b/i', $response)) {
            $response .= "\n\n⚠️ This is AI-generated guidance, not medical advice. Please consult your healthcare provider.";
        }

        return $response;
    }

    /**
     * Check if input appears to request medical diagnosis.
     */
    public function isDiagnosisRequest(string $input): bool
    {
        foreach (self::BLOCKED_PATTERNS as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }
}
```

### 8.2 Integrate Guardrails into BedrockService

In `BedrockService::ask()`, wrap the input/output:

```php
public function __construct(
    private readonly GuardrailService $guardrails,  // ADD
) {}

public function ask(string $systemPrompt, string $userMessage, array $options = []): array
{
    // Sanitize input
    $userMessage = $this->guardrails->sanitizeInput($userMessage);

    // ... existing invoke logic ...

    // Validate output
    if ($result['success']) {
        $result['response'] = $this->guardrails->validateResponse($result['response']);
    }

    return $result;
}
```

---

## 9. Database Migrations

### 9.1 Migration: `create_ai_settings_table.php`

```php
Schema::create('ai_settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->json('value')->nullable();
    $table->string('description')->nullable();
    $table->timestamps();
});
```

**Default seed data:**

| key | value | description |
|---|---|---|
| `ai_enabled` | `true` | Master switch for AI features |
| `default_model` | `"anthropic.claude-3-5-sonnet-20241022-v2:0"` | Default model for all AI calls |
| `fast_model` | `"anthropic.claude-3-haiku-20240307-v1:0"` | Fast/cheap model for alerts |
| `rag_ai_enabled` | `true` | Use AI for RAG answer synthesis |
| `simulation_ai_enabled` | `true` | Use AI for simulation explanations |
| `alert_ai_enabled` | `true` | Use AI for alert messages |
| `max_tokens_rag` | `512` | Max tokens for RAG responses |
| `max_tokens_simulation` | `384` | Max tokens for simulation explanations |
| `temperature` | `0.3` | Default temperature |

### 9.2 Migration: `add_ai_columns_to_rag_query_logs.php`

```php
Schema::table('rag_query_logs', function (Blueprint $table) {
    $table->string('model_used')->nullable()->after('confidence');
    $table->integer('tokens_used')->nullable()->after('model_used');
    $table->decimal('ai_cost', 8, 6)->nullable()->after('tokens_used');
    $table->integer('latency_ms')->nullable()->after('ai_cost');
});
```

---

## 10. System Prompts

All system prompts are centralized in `PromptTemplates.php` (Section 3.2). Summary:

| Integration Point | Prompt Purpose | Max Tokens | Model |
|---|---|---|---|
| RAG Synthesis | Synthesize knowledge base excerpts into clear answer | 512 | smart (Claude 3.5) |
| Simulation Explanation | Explain lifestyle change impact on metabolic health | 384 | smart |
| Food Impact Analysis | Analyze food's hormonal/metabolic impact, suggest alternatives | 512 | smart |
| Alert Enhancement | Generate contextual, actionable alert messages | 128 | fast (Claude Haiku) |
| Risk Narrative | Summarize risk profile with contributing factors | 256 | smart |

**Shared prompt rules across all templates:**
- Never diagnose or prescribe medication
- Always recommend consulting healthcare providers
- Use simple, patient-friendly language
- Keep responses concise (word limits specified per template)
- Empathetic, encouraging tone

---

## 11. File-by-File Change Map

### New Files to Create

| File | Purpose |
|---|---|
| `app/Services/AI/BedrockService.php` | Central wrapper around Bedrock Facade |
| `app/Services/AI/PromptTemplates.php` | All system prompt templates |
| `app/Services/AI/GuardrailService.php` | Input/output safety validation |
| `app/Models/AiSetting.php` | Admin AI settings model |
| `app/Http/Controllers/Admin/BedrockManagementController.php` | Admin Bedrock API |
| `app/Http/Controllers/Admin/BedrockSettingsController.php` | Admin AI settings API |
| `resources/views/admin/bedrock/index.blade.php` | AI Dashboard view |
| `resources/views/admin/bedrock/models.blade.php` | Model Explorer view |
| `resources/views/admin/bedrock/usage.blade.php` | Usage Analytics view |
| `database/migrations/xxxx_create_ai_settings_table.php` | AI settings migration |
| `database/migrations/xxxx_add_ai_columns_to_rag_query_logs.php` | Query log AI fields |
| `database/seeders/AiSettingSeeder.php` | Default AI settings |

### Existing Files to Modify

| File | Change |
|---|---|
| `app/Services/Rag/RagAnswerBuilder.php` | Add BedrockService DI, call Claude for synthesis |
| `app/Services/Rag/RagSearchService.php` | Pass `$question` to `build()`, include `ai_metadata` in return |
| `app/Services/Simulation/SimulationService.php` | Add BedrockService DI, generate AI explanations |
| `app/Services/Alerts/AlertService.php` | Add BedrockService DI, enhance alert messages |
| `app/Http/Controllers/RagController.php` | Add `stream()` method, return `ai_metadata` |
| `app/Providers/RagServiceProvider.php` | Register `BedrockService` singleton |
| `routes/api.php` | Add Bedrock admin routes + streaming endpoint |
| `routes/web.php` | Add Bedrock admin page routes |
| `app/Http/Controllers/Web/Admin/PageController.php` | Add `bedrock()`, `bedrockModels()`, `bedrockUsage()` |
| `resources/views/layouts/admin.blade.php` | Add AI Engine nav section in sidebar |
| `.env.example` | Add Bedrock env variables |

### Files NOT Changed (by design)

| File | Reason |
|---|---|
| `app/Services/Risk/RiskEngineService.php` | Math-based scoring stays pure; AI narratives wrap results externally |
| `app/Services/DigitalTwin/DigitalTwinService.php` | Twin generation is data-driven, no LLM needed |
| `app/Models/Simulation.php` | Existing `rag_explanation` column stores AI output without schema change |
| `config/bedrock.php` | Created by `vendor:publish`, not manually authored |

---

## 12. Testing Strategy

### Unit Tests

| Test File | What It Tests |
|---|---|
| `tests/Unit/Services/AI/BedrockServiceTest.php` | Mocks Bedrock Facade, tests ask/fallback/error flows |
| `tests/Unit/Services/AI/GuardrailServiceTest.php` | Tests sanitization, validation, diagnosis detection |
| `tests/Unit/Services/AI/PromptTemplatesTest.php` | Verifies all templates return non-empty strings |

### Feature Tests

| Test File | What It Tests |
|---|---|
| `tests/Feature/RagAiIntegrationTest.php` | RAG query returns AI-synthesized answers (mocked Bedrock) |
| `tests/Feature/SimulationAiTest.php` | Simulation runs with AI explanation (mocked) |
| `tests/Feature/Admin/BedrockManagementTest.php` | Admin endpoints return correct structure |

### Mocking Strategy

Use the package's test helpers:

```php
use Ubxty\BedrockAi\Facades\Bedrock;

Bedrock::fake([
    'response'     => 'Mocked AI response for testing.',
    'input_tokens' => 100,
    'output_tokens'=> 50,
    'total_tokens' => 150,
    'cost'         => 0.001,
    'latency_ms'   => 200,
    'status'       => 'success',
    'key_used'     => 'test-key',
    'model_id'     => 'anthropic.claude-3-5-sonnet-20241022-v2:0',
]);
```

---

## 13. Environment Variables

Complete list of new `.env` variables:

```env
# ── Amazon Bedrock (ubxty/bedrock-ai) ────
BEDROCK_AWS_KEY="${AWS_ACCESS_KEY_ID}"
BEDROCK_AWS_SECRET="${AWS_SECRET_ACCESS_KEY}"
BEDROCK_REGION=us-east-1

# Cost controls
BEDROCK_DAILY_LIMIT=10.00
BEDROCK_MONTHLY_LIMIT=100.00

# Logging
BEDROCK_LOGGING_ENABLED=true
BEDROCK_LOG_CHANNEL=stack

# Multi-key rotation (optional)
# BEDROCK_AWS_KEY_2=
# BEDROCK_AWS_SECRET_2=
# BEDROCK_LABEL_2=backup

# Pricing API (optional, for real-time pricing)
# BEDROCK_PRICING_KEY=
# BEDROCK_PRICING_SECRET=
# BEDROCK_PRICING_REGION=us-east-1
```

---

## Implementation Order

| Order | Phase | Effort | Dependency |
|---|---|---|---|
| 1 | Phase 0: Install & Configure | ~15 min | None |
| 2 | Phase 1: Core AI Service Layer | ~1 hr | Phase 0 |
| 3 | Phase 2: RAG LLM Enhancement | ~1 hr | Phase 1 |
| 4 | Phase 3: Simulation & Alert AI | ~1.5 hr | Phase 1 |
| 5 | Phase 4: Admin Management UI | ~2 hr | Phase 1 |
| 6 | Phase 5: Streaming | ~1 hr | Phase 2 |
| 7 | Phase 6: Guardrails | ~30 min | Phase 1 |
| 8 | Database Migrations | ~15 min | Before Phase 4 |
| 9 | Testing | ~1 hr | All phases |

**Critical Path**: Phase 0 → Phase 1 → Phase 2 (RAG AI is the highest-impact feature for the hackathon demo)

---

## Quick Win Strategy (Hackathon Demo Priority)

If time is limited, implement in this order for maximum demo impact:

1. **Phase 0 + Phase 1** — Get Bedrock working, `BedrockService` ready
2. **Phase 2** — RAG answers become AI-synthesized (immediate "wow" factor)
3. **Phase 3.1** — Simulation explanations become AI-generated
4. **Phase 4 (partial)** — AI Dashboard status card only (shows judges the admin management exists)
5. **Everything else** — Fill in as time allows

This gives you a working AI-powered RAG system and simulation engine with just ~2.5 hours of coding work.
