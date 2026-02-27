<?php

namespace App\Enums;

enum RiskCategory: string
{
    case LOW = 'low';
    case MODERATE = 'moderate';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public static function fromScore(float $score): self
    {
        return match (true) {
            $score <= 30 => self::LOW,
            $score <= 55 => self::MODERATE,
            $score <= 75 => self::HIGH,
            default => self::CRITICAL,
        };
    }
}
