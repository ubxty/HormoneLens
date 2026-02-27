<?php

namespace App\Contracts;

interface RagSearchInterface
{
    /**
     * Search the RAG knowledge base for an answer.
     *
     * @param string $question The user's question
     * @param string|null $diseaseContext 'diabetes' or 'pcod' or null
     * @return array{answer: string, reasoning_path: array, source_nodes: array, source_pages: array, confidence: float}
     */
    public function search(string $question, ?string $diseaseContext = null): array;
}
