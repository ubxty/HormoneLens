<?php

namespace App\Models;

use App\Enums\CravingFrequency;
use App\Enums\Frequency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiseaseDiabetes extends Model
{
    protected $table = 'disease_diabetes';

    protected $fillable = [
        'user_id',
        'avg_blood_sugar',
        'family_history',
        'frequent_urination',
        'excessive_thirst',
        'fatigue',
        'blurred_vision',
        'numbness_tingling',
        'slow_wound_healing',
        'unexplained_weight_loss',
        'sugar_cravings',
        'energy_crashes_after_meals',
    ];

    protected function casts(): array
    {
        return [
            'avg_blood_sugar' => 'decimal:1',
            'family_history' => 'boolean',
            'frequent_urination' => Frequency::class,
            'excessive_thirst' => Frequency::class,
            'fatigue' => Frequency::class,
            'blurred_vision' => Frequency::class,
            'numbness_tingling' => 'boolean',
            'slow_wound_healing' => 'boolean',
            'unexplained_weight_loss' => 'boolean',
            'sugar_cravings' => CravingFrequency::class,
            'energy_crashes_after_meals' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
