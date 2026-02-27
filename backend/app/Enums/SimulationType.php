<?php

namespace App\Enums;

enum SimulationType: string
{
    case MEAL = 'meal';
    case SLEEP = 'sleep';
    case STRESS = 'stress';
    case FOOD_IMPACT = 'food_impact';
}
