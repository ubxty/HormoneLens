<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Disease extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'icon',
        'description',
        'is_active',
        'sort_order',
        'risk_weights',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'risk_weights' => 'array',
        ];
    }

    // ── Relationships ────────────────────────────────

    public function fields(): HasMany
    {
        return $this->hasMany(DiseaseField::class)->orderBy('sort_order');
    }

    public function userData(): HasMany
    {
        return $this->hasMany(UserDiseaseData::class);
    }

    // ── Scopes ───────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ── Helpers ──────────────────────────────────────

    /**
     * Get fields grouped by category.
     */
    public function getFieldsByCategory(): array
    {
        return $this->fields->groupBy('category')->toArray();
    }

    /**
     * Build Laravel validation rules from field definitions.
     */
    public function buildValidationRules(): array
    {
        $rules = [];
        foreach ($this->fields as $field) {
            $fieldRules = [];

            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Merge custom validation rules
            if ($field->validation && isset($field->validation['rules'])) {
                $fieldRules = array_merge($fieldRules, $field->validation['rules']);
            } else {
                // Infer from field_type
                $fieldRules[] = match ($field->field_type) {
                    'number' => 'numeric',
                    'boolean' => 'boolean',
                    'select' => 'string',
                    'text' => 'string|max:1000',
                    default => 'string',
                };
            }

            $rules["field_values.{$field->slug}"] = $fieldRules;
        }

        return $rules;
    }
}
