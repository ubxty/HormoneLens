<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RagNode extends Model
{
    protected $fillable = [
        'document_id',
        'parent_id',
        'title',
        'summary',
        'keywords',
        'depth',
    ];

    protected function casts(): array
    {
        return [
            'depth' => 'integer',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(RagDocument::class, 'document_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(RagPage::class, 'node_id');
    }

    /**
     * Get keywords as an array.
     */
    public function getKeywordsArrayAttribute(): array
    {
        return array_map('trim', explode(',', strtolower($this->keywords)));
    }
}
