<?php

namespace App\Models;

use App\Enums\RiskCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DigitalTwin extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'metabolic_health_score',
        'insulin_resistance_score',
        'sleep_score',
        'stress_score',
        'diet_score',
        'overall_risk_score',
        'risk_category',
        'snapshot_data',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'metabolic_health_score' => 'decimal:2',
            'insulin_resistance_score' => 'decimal:2',
            'sleep_score' => 'decimal:2',
            'stress_score' => 'decimal:2',
            'diet_score' => 'decimal:2',
            'overall_risk_score' => 'decimal:2',
            'risk_category' => RiskCategory::class,
            'snapshot_data' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function simulations(): HasMany
    {
        return $this->hasMany(Simulation::class);
    }
}
