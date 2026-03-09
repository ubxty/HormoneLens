<?php

namespace App\Services\Rag;

class RagConfidenceService
{
    /**
     * Calculate confidence score based on traversal depth, keyword matches, and source page count.
     *
     * Improved formula (R4): uses logarithmic decay for keyword matches to prevent
     * easy saturation, and weights depth more heavily since deeper traversal
     * indicates more specific knowledge was found.
     */
    public function calculate(int $depthReached, int $totalKeywordMatches, int $sourcePageCount = 0): float
    {
        // Base confidence from depth (deeper = more specific = more confident)
        // Each level adds diminishing returns: 20 + 15 + 12 + 10 + ...
        $depthScore = 0;
        for ($i = 1; $i <= $depthReached; $i++) {
            $depthScore += max(5, 25 / $i);
        }

        // Keyword match score with logarithmic scaling (prevents easy saturation)
        $keywordScore = $totalKeywordMatches > 0
            ? 15 * log(1 + $totalKeywordMatches)
            : 0;

        // Source page bonus: having actual content pages increases confidence
        $pageScore = min(15, $sourcePageCount * 5);

        $confidence = $depthScore + $keywordScore + $pageScore;

        return min(95.0, max(0.0, round($confidence, 1)));
    }
}
