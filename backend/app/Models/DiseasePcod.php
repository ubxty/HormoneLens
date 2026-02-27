<?php

namespace App\Models;

use App\Enums\CravingFrequency;
use App\Enums\CycleRegularity;
use App\Enums\Frequency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiseasePcod extends Model
{
    protected $table = 'disease_pcod';

    protected $fillable = [
        'user_id',
        'cycle_regularity',
        'avg_cycle_length_days',
        'excess_facial_body_hair',
        'acne_oily_skin',
        'hair_thinning',
        'weight_gain_difficulty_losing',
        'mood_swings_anxiety',
        'dark_skin_patches',
        'fatigue_frequency',
        'sleep_disturbances',
        'sugar_cravings',
        'insulin_resistance_diagnosed',
    ];

    protected function casts(): array
    {
        return [
            'cycle_regularity' => CycleRegularity::class,
            'avg_cycle_length_days' => 'integer',
            'excess_facial_body_hair' => 'boolean',
            'acne_oily_skin' => 'boolean',
            'hair_thinning' => 'boolean',
            'weight_gain_difficulty_losing' => 'boolean',
            'mood_swings_anxiety' => 'boolean',
            'dark_skin_patches' => 'boolean',
            'fatigue_frequency' => Frequency::class,
            'sleep_disturbances' => Frequency::class,
            'sugar_cravings' => CravingFrequency::class,
            'insulin_resistance_diagnosed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
