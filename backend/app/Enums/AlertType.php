<?php

namespace App\Enums;

enum AlertType: string
{
    case RISK_THRESHOLD = 'risk_threshold';
    case HIGH_GI = 'high_gi';
    case LOW_SLEEP = 'low_sleep';
    case HIGH_STRESS = 'high_stress';
    case REPEATED_RISK = 'repeated_risk';
}
