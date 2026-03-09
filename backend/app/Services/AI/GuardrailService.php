<?php

namespace App\Services\AI;

class GuardrailService
{
    private const MAX_INPUT_LENGTH = 2000;

    /**
     * Patterns that indicate prompt injection attempts.
     */
    private const INJECTION_PATTERNS = [
        '/\b(SYSTEM|ASSISTANT|HUMAN):/i',
        '/\bignore\s+(all\s+)?(previous|above|prior)\s+(instructions?|prompts?|rules?)/i',
        '/\bforget\s+(everything|all|your)\b/i',
        '/\byou\s+are\s+now\b/i',
        '/\bact\s+as\s+(if|a)\b/i',
        '/\bnew\s+instructions?\s*:/i',
        '/\boverride\s+(system|safety|guardrail)/i',
        '/\bdisregard\s+(safety|previous|all)/i',
        '/\bpretend\s+(you|to\s+be)/i',
        '/\bjailbreak/i',
        '/\bDAN\s*mode/i',
        '/```\s*(system|prompt)/i',
        '/\[\s*(SYSTEM|INST)\s*\]/i',
        '/<\|im_start\|>/i',
    ];

    /**
     * Sanitize user input before sending to Bedrock.
     */
    public function sanitizeInput(string $input): string
    {
        $input = mb_substr(trim($input), 0, self::MAX_INPUT_LENGTH);

        // Remove prompt injection patterns
        foreach (self::INJECTION_PATTERNS as $pattern) {
            $input = preg_replace($pattern, '', $input);
        }

        // Remove excessive whitespace left from removals
        $input = preg_replace('/\s{3,}/', ' ', $input);

        return trim($input);
    }

    /**
     * Check if input contains prompt injection attempts.
     */
    public function containsInjection(string $input): bool
    {
        foreach (self::INJECTION_PATTERNS as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validate AI response before returning to user.
     */
    public function validateResponse(string $response): string
    {
        if (preg_match('/\b(recommend|should|try|consider|avoid)\b/i', $response)) {
            $response .= "\n\n⚠️ This is AI-generated guidance, not medical advice. Please consult your healthcare provider.";
        }

        return $response;
    }

    /**
     * Check if input appears to request medical diagnosis.
     */
    public function isDiagnosisRequest(string $input): bool
    {
        $patterns = [
            '/\b(prescri(?:be|ption)|diagnos(?:e|is)|medicat(?:e|ion))\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }
}
