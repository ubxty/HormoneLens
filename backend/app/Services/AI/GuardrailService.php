<?php

namespace App\Services\AI;

class GuardrailService
{
    private const MAX_INPUT_LENGTH = 2000;

    /**
     * Sanitize user input before sending to Bedrock.
     */
    public function sanitizeInput(string $input): string
    {
        $input = mb_substr(trim($input), 0, self::MAX_INPUT_LENGTH);

        // Remove potential prompt injection markers
        $input = preg_replace('/\b(SYSTEM|ASSISTANT|HUMAN):/i', '', $input);

        return $input;
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
