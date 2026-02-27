<?php

namespace App\Models;

use App\Enums\AlertType;
use App\Enums\Severity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'user_id',
        'simulation_id',
        'type',
        'title',
        'message',
        'severity',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'type' => AlertType::class,
            'severity' => Severity::class,
            'is_read' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function simulation(): BelongsTo
    {
        return $this->belongsTo(Simulation::class);
    }
}
