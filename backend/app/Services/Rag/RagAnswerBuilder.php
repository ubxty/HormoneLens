<?php

namespace App\Services\Rag;

use App\Models\RagPage;
use App\Repositories\Rag\RagPageRepository;

class RagAnswerBuilder
{
    public function __construct(
        private readonly RagPageRepository $pageRepo,
    ) {}

    /**
     * Build a structured answer from terminal nodes.
     */
    public function build(array $terminalNodes, array $path): array
    {
        $nodeIds = collect($terminalNodes)->pluck('id')->toArray();
        $pages = $this->pageRepo->getByNodeIds($nodeIds);

        if ($pages->isEmpty()) {
            return [
                'answer' => 'No relevant information found in the knowledge base.',
                'source_pages' => [],
            ];
        }

        // Build answer by concatenating page content
        $answerParts = [];
        foreach ($pages as $page) {
            $answerParts[] = $page->content;
        }

        $answer = implode("\n\n", $answerParts);

        // Trim to reasonable length (first 2000 chars)
        if (strlen($answer) > 2000) {
            $answer = substr($answer, 0, 2000) . '...';
        }

        return [
            'answer' => $answer,
            'source_pages' => $pages->pluck('id')->toArray(),
        ];
    }
}
