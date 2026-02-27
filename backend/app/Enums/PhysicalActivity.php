<?php

namespace App\Enums;

enum PhysicalActivity: string
{
    case SEDENTARY = 'sedentary';
    case MODERATE = 'moderate';
    case ACTIVE = 'active';
}
