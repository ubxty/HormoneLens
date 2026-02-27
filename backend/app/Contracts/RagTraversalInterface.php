<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface RagTraversalInterface
{
    /**
     * Traverse the node tree from scored roots.
     *
     * @param Collection $rootNodes Root-level RAG nodes
     * @param array $tokens Tokenized question keywords
     * @param string|null $diseaseContext 'diabetes' or 'pcod' or null
     * @return array{path: array, terminal_nodes: array, total_keyword_matches: int}
     */
    public function traverse(Collection $rootNodes, array $tokens, ?string $diseaseContext = null): array;
}
