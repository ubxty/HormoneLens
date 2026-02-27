<?php

namespace App\Services\Rag;

use App\Contracts\RagTraversalInterface;
use App\Repositories\Rag\RagNodeRepository;
use Illuminate\Database\Eloquent\Collection;

class RagTraversalEngine implements RagTraversalInterface
{
    public function __construct(
        private readonly RagScoringService $scoring,
        private readonly RagNodeRepository $nodeRepo,
    ) {}

    /**
     * Traverse the node tree from scored roots.
     * Returns the best path, terminal nodes, and total keyword matches.
     */
    public function traverse(Collection $rootNodes, array $tokens, ?string $diseaseContext = null): array
    {
        if ($rootNodes->isEmpty()) {
            return ['path' => [], 'terminal_nodes' => [], 'total_keyword_matches' => 0];
        }

        // Score all root nodes
        $scored = $rootNodes->map(function ($node) use ($tokens, $diseaseContext) {
            $keywords = array_map('trim', explode(',', strtolower($node->keywords)));
            $score = $this->scoring->scoreNode($keywords, $tokens, $diseaseContext);
            return ['node' => $node, 'score' => $score];
        })->sortByDesc('score');

        $best = $scored->first();
        if ($best['score'] === 0) {
            // No matches at all — return first root as fallback
            $best = $scored->first();
        }

        $currentNode = $best['node'];
        $currentScore = $best['score'];
        $path = [$currentNode];
        $totalMatches = $currentScore;

        // Traverse down
        while (true) {
            $children = $this->nodeRepo->getChildren($currentNode->id);
            if ($children->isEmpty()) {
                break;
            }

            $bestChild = null;
            $bestChildScore = 0;

            foreach ($children as $child) {
                $keywords = array_map('trim', explode(',', strtolower($child->keywords)));
                $score = $this->scoring->scoreNode($keywords, $tokens, $diseaseContext);
                if ($score > $bestChildScore) {
                    $bestChild = $child;
                    $bestChildScore = $score;
                }
            }

            // Stop if no child scores higher than current
            if (!$bestChild || $bestChildScore <= 0) {
                break;
            }

            $currentNode = $bestChild;
            $currentScore = $bestChildScore;
            $totalMatches += $bestChildScore;
            $path[] = $currentNode;
        }

        // Terminal nodes = last 1-2 in path
        $terminalNodes = array_slice($path, -2);

        return [
            'path' => $path,
            'terminal_nodes' => $terminalNodes,
            'total_keyword_matches' => $totalMatches,
        ];
    }
}
