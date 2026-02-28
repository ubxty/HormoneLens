<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimulationResult extends Model
{
    protected $fillable = [
        'user_id',
        'metabolic_score',
        'insulin_score',
        'sleep_score',
        'stress_score',
        'diet_score',
        'pcos_risk',
        'diabetes_risk',
        'insulin_resistance_risk',
    ];

    protected function casts(): array
    {
        return [
            'metabolic_score'         => 'decimal:2',
            'insulin_score'           => 'decimal:2',
            'sleep_score'             => 'decimal:2',
            'stress_score'            => 'decimal:2',
            'diet_score'              => 'decimal:2',
            'pcos_risk'               => 'decimal:2',
            'diabetes_risk'           => 'decimal:2',
            'insulin_resistance_risk' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
