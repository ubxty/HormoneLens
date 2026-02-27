<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RagQueryLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'question',
        'reasoning_path',
        'selected_nodes',
        'confidence',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'reasoning_path' => 'array',
            'selected_nodes' => 'array',
            'confidence' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
