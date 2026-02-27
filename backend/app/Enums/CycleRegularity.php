<?php

namespace App\Enums;

enum CycleRegularity: string
{
    case REGULAR = 'regular';
    case IRREGULAR = 'irregular';
    case MISSED = 'missed';
}
