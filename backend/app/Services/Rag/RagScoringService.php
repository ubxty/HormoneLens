<?php

namespace App\Services\Rag;

class RagScoringService
{
    /**
     * Score a node based on keyword matches and disease context.
     */
    public function scoreNode(array $nodeKeywords, array $tokens, ?string $diseaseContext = null): int
    {
        $score = 0;

        foreach ($tokens as $token) {
            foreach ($nodeKeywords as $keyword) {
                if (str_contains($keyword, $token) || str_contains($token, $keyword)) {
                    $score++;
                }
            }
        }

        // Disease context bonus
        if ($diseaseContext && in_array($diseaseContext, $nodeKeywords, true)) {
            $score += 2;
        }

        return $score;
    }

    /**
     * Tokenize a question: lowercase, remove stopwords, extract meaningful tokens.
     */
    public function tokenize(string $question): array
    {
        $stopwords = [
            'how', 'does', 'do', 'is', 'are', 'was', 'were', 'what', 'which',
            'who', 'whom', 'this', 'that', 'these', 'those', 'am', 'be', 'been',
            'being', 'have', 'has', 'had', 'having', 'will', 'would', 'shall',
            'should', 'may', 'might', 'must', 'can', 'could', 'the', 'a', 'an',
            'and', 'but', 'or', 'nor', 'not', 'so', 'yet', 'both', 'either',
            'neither', 'each', 'every', 'all', 'any', 'few', 'more', 'most',
            'of', 'in', 'on', 'at', 'to', 'for', 'with', 'about', 'against',
            'between', 'through', 'during', 'before', 'after', 'above', 'below',
            'from', 'up', 'down', 'out', 'off', 'over', 'under', 'again', 'then',
            'once', 'here', 'there', 'when', 'where', 'why', 'my', 'your', 'its',
            'i', 'me', 'we', 'you', 'he', 'she', 'it', 'they', 'them',
        ];

        $words = preg_split('/[\s\-_,;:.!?]+/', strtolower(trim($question)));

        return array_values(array_filter($words, function ($word) use ($stopwords) {
            return strlen($word) > 2 && !in_array($word, $stopwords, true);
        }));
    }
}
