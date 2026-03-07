<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodGlycemicData extends Model
{
    protected $table = 'food_glycemic_data';

    protected $fillable = [
        'food_item',
        'category',
        'glycemic_index',
        'glycemic_load',
        'typical_spike_mg_dl',
        'peak_time_minutes',
        'recovery_time_minutes',
        'serving_size',
        'alternatives',
    ];

    protected function casts(): array
    {
        return [
            'alternatives' => 'array',
        ];
    }

    /**
     * Find a food entry by fuzzy name matching.
     */
    public static function findByName(string $query): ?self
    {
        $query = strtolower(trim($query));

        // Exact match first
        $exact = static::whereRaw('LOWER(food_item) = ?', [$query])->first();
        if ($exact) {
            return $exact;
        }

        // Partial match
        return static::whereRaw('LOWER(food_item) LIKE ?', ["%{$query}%"])->first();
    }
}
