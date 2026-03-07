<?php

namespace App\Services\Rag;

use App\Contracts\RagSearchInterface;
use App\Contracts\RagTraversalInterface;
use App\Models\RagQueryLog;
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
        $confidenceScore = $this->confidence->calculate($depthReached, $totalMatches);

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

        RagQueryLog::create([
            'user_id' => $userId,
            'question' => $question,
            'reasoning_path' => $result['reasoning_path'],
            'selected_nodes' => $result['source_nodes'],
            'confidence' => $result['confidence'],
            'created_at' => now(),
        ]);

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
