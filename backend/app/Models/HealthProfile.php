<?php

namespace App\Models;

use App\Enums\DiseaseType;
use App\Enums\PhysicalActivity;
use App\Enums\StressLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthProfile extends Model
{
    protected $fillable = [
        'user_id',
        'weight',
        'height',
        'avg_sleep_hours',
        'stress_level',
        'physical_activity',
        'eating_habits',
        'water_intake',
        'disease_type',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'height' => 'decimal:2',
            'avg_sleep_hours' => 'decimal:1',
            'water_intake' => 'decimal:2',
            'stress_level' => StressLevel::class,
            'physical_activity' => PhysicalActivity::class,
            'disease_type' => DiseaseType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
