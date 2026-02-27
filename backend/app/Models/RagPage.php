<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RagPage extends Model
{
    protected $fillable = [
        'node_id',
        'page_number',
        'content',
    ];

    protected function casts(): array
    {
        return [
            'page_number' => 'integer',
        ];
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(RagNode::class, 'node_id');
    }
}
