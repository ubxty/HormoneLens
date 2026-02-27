<?php

namespace App\Models;

use App\Enums\RiskCategory;
use App\Enums\SimulationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Simulation extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'digital_twin_id',
        'type',
        'input_data',
        'modified_twin_data',
        'original_risk_score',
        'simulated_risk_score',
        'risk_change',
        'risk_category_before',
        'risk_category_after',
        'rag_explanation',
        'rag_confidence',
        'results',
    ];

    protected function casts(): array
    {
        return [
            'type' => SimulationType::class,
            'input_data' => 'array',
            'modified_twin_data' => 'array',
            'original_risk_score' => 'decimal:2',
            'simulated_risk_score' => 'decimal:2',
            'risk_change' => 'decimal:2',
            'risk_category_before' => RiskCategory::class,
            'risk_category_after' => RiskCategory::class,
            'rag_confidence' => 'decimal:2',
            'results' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function digitalTwin(): BelongsTo
    {
        return $this->belongsTo(DigitalTwin::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
}
