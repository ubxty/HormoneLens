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

    /**
     * Build a structured answer from terminal nodes.
     * Uses LLM synthesis when available, falls back to concatenation.
     */
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

        $context = $pages->map(fn(RagPage $p) => $p->content)->implode("\n\n---\n\n");
        $pathStr = collect($path)->pluck('title')->implode(' → ');

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
                'model'   => $aiResult['model_used'],
                'tokens'  => $aiResult['input_tokens'] + $aiResult['output_tokens'],
                'cost'    => $aiResult['cost'],
                'latency' => $aiResult['latency_ms'],
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

        // Fall back to concatenated text if AI fails
        if (!$result['success']) {
            $truncated = mb_substr($context, 0, 2000);
            $result['response'] = $truncated . (mb_strlen($context) > 2000 ? '...' : '');
        }

        return $result;
    }
}
