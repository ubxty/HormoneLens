<?php

namespace App\Repositories\Rag;

use App\Models\RagNode;
use Illuminate\Database\Eloquent\Collection;

class RagNodeRepository
{
    public function getRootNodes(): Collection
    {
        return RagNode::whereNull('parent_id')->get();
    }

    public function getChildren(int $nodeId): Collection
    {
        return RagNode::where('parent_id', $nodeId)->get();
    }

    public function findById(int $id): ?RagNode
    {
        return RagNode::find($id);
    }

    public function create(array $data): RagNode
    {
        return RagNode::create($data);
    }
}
