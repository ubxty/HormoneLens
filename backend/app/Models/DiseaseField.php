<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiseaseField extends Model
{
    protected $fillable = [
        'disease_id',
        'slug',
        'label',
        'field_type',
        'category',
        'options',
        'validation',
        'risk_config',
        'sort_order',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'validation' => 'array',
            'risk_config' => 'array',
            'sort_order' => 'integer',
            'is_required' => 'boolean',
        ];
    }

    public function disease(): BelongsTo
    {
        return $this->belongsTo(Disease::class);
    }
}
