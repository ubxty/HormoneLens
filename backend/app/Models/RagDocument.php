<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RagDocument extends Model
{
    protected $fillable = ['title', 'description'];

    public function nodes(): HasMany
    {
        return $this->hasMany(RagNode::class, 'document_id');
    }

    public function rootNodes(): HasMany
    {
        return $this->hasMany(RagNode::class, 'document_id')->whereNull('parent_id');
    }
}
