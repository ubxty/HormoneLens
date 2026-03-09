<?php

namespace App\Services\Rag;

use App\Contracts\RagSearchInterface;
use App\Contracts\RagTraversalInterface;
use App\Models\RagQueryLog;
use App\Models\Simulation;
use App\Repositories\Rag\RagNodeRepository;

class RagSearchService implements RagSearchInterface
{
    public function __construct(
        private readonly RagScoringService $scoring,
        private readonly RagTraversalInterface $traversal,
        private readonly RagAnswerBuilder $answerBuilder,
        private readonly RagConfidenceService $confidence,
        private readonly RagNodeRepository $nodeRepo,
    ) {}

    /**
     * Search the RAG knowledge base for an answer.
     */
    public function search(string $question, ?string $diseaseContext = null): array
    {
        // Step 1: Tokenize
        $tokens = $this->scoring->tokenize($question);

        if (empty($tokens)) {
            return $this->emptyResult();
        }

        // Step 2: Get root nodes and traverse
        $rootNodes = $this->nodeRepo->getRootNodes();

        if ($rootNodes->isEmpty()) {
            return $this->emptyResult();
        }

        // Step 3: Traverse tree
        $traversalResult = $this->traversal->traverse($rootNodes, $tokens, $diseaseContext);

        if (empty($traversalResult['path'])) {
            return $this->emptyResult();
        }

        // Step 4: Build answer from terminal nodes
        $answerData = $this->answerBuilder->build(
            $traversalResult['terminal_nodes'],
            $traversalResult['path'],
            $question
        );

        // Step 5: Calculate confidence
        $depthReached = count($traversalResult['path']);
        $totalMatches = $traversalResult['total_keyword_matches'];
        $sourcePageCount = count($answerData['source_pages'] ?? []);
        $confidenceScore = $this->confidence->calculate($depthReached, $totalMatches, $sourcePageCount);

        // Step 6: Build reasoning path
        $reasoningPath = collect($traversalResult['path'])->map(function ($node, $index) {
            $prefix = $index === 0 ? 'Root: ' : '→ ';
            return $prefix . $node->title;
        })->toArray();

        $sourceNodes = collect($traversalResult['path'])->pluck('id')->toArray();

        return [
            'answer' => $answerData['answer'],
            'reasoning_path' => $reasoningPath,
            'source_nodes' => $sourceNodes,
            'source_pages' => $answerData['source_pages'],
            'confidence' => $confidenceScore,
            'ai_metadata' => $answerData['ai_metadata'] ?? null,
        ];
    }

    /**
     * Search and log the query.
     */
    public function searchAndLog(string $question, ?string $diseaseContext, int $userId): array
    {
        $result = $this->search($question, $diseaseContext);

        // Enrich answer with user simulation history (R2)
        $result = $this->enrichWithUserHistory($result, $userId, $diseaseContext);

        RagQueryLog::create([
            'user_id' => $userId,
            'question' => $question,
            'reasoning_path' => $result['reasoning_path'],
            'selected_nodes' => $result['source_nodes'],
            'confidence' => $result['confidence'],
            'model_used' => $result['ai_metadata']['model'] ?? null,
            'tokens_used' => $result['ai_metadata']['tokens'] ?? null,
            'ai_cost' => $result['ai_metadata']['cost'] ?? null,
            'latency_ms' => $result['ai_metadata']['latency'] ?? null,
            'created_at' => now(),
        ]);

        return $result;
    }

    /**
     * Enrich RAG answer with user's recent simulation context (R2).
     * Appends a summary of recent simulations so RAG answers are personalized.
     */
    private function enrichWithUserHistory(array $result, int $userId, ?string $diseaseContext): array
    {
        $recentSims = Simulation::where('user_id', $userId)
            ->latest()
            ->limit(5)
            ->get(['type', 'risk_change', 'simulated_risk_score', 'risk_category_after', 'created_at']);

        if ($recentSims->isEmpty()) {
            return $result;
        }

        $historyLines = $recentSims->map(function ($sim) {
            $change = $sim->risk_change > 0 ? "+{$sim->risk_change}" : (string) $sim->risk_change;
            return "- {$sim->type->value} simulation: risk {$change} (score: {$sim->simulated_risk_score}, category: {$sim->risk_category_after->value})";
        })->implode("\n");

        $result['answer'] .= "\n\n**Your Recent Simulation History:**\n{$historyLines}";
        $result['user_context'] = [
            'recent_simulations' => $recentSims->count(),
            'latest_risk_category' => $recentSims->first()->risk_category_after->value,
        ];

        return $result;
    }

    private function emptyResult(): array
    {
        return [
            'answer' => 'No relevant information found for your query.',
            'reasoning_path' => [],
            'source_nodes' => [],
            'source_pages' => [],
            'confidence' => 0.0,
            'ai_metadata' => null,
        ];
    }
}
