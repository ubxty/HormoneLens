<?php

namespace App\Services\Rag;

class RagConfidenceService
{
    /**
     * Calculate confidence score based on traversal depth and keyword matches.
     *
     * Formula: Base 60 + (10 × depth reached) + (5 × keyword matches), Max 95
     */
    public function calculate(int $depthReached, int $totalKeywordMatches): float
    {
        $confidence = 60 + (10 * $depthReached) + (5 * $totalKeywordMatches);

        return min(95.0, max(0.0, (float) $confidence));
    }
}
