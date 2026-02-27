<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDiseaseData extends Model
{
    protected $table = 'user_disease_data';

    protected $fillable = [
        'user_id',
        'disease_id',
        'field_values',
    ];

    protected function casts(): array
    {
        return [
            'field_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function disease(): BelongsTo
    {
        return $this->belongsTo(Disease::class);
    }

    /**
     * Get a specific field value with optional default.
     */
    public function getValue(string $fieldSlug, mixed $default = null): mixed
    {
        return $this->field_values[$fieldSlug] ?? $default;
    }

    /**
     * Convert to a flat associative array (for snapshot compatibility).
     */
    public function toFlatArray(): array
    {
        return array_merge(
            ['disease_id' => $this->disease_id],
            $this->field_values ?? [],
        );
    }
}
