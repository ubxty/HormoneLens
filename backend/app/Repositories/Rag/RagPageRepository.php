<?php

namespace App\Repositories\Rag;

use App\Models\RagPage;
use Illuminate\Database\Eloquent\Collection;

class RagPageRepository
{
    public function getByNodeIds(array $nodeIds): Collection
    {
        return RagPage::whereIn('node_id', $nodeIds)
            ->orderBy('node_id')
            ->orderBy('page_number')
            ->get();
    }

    public function create(array $data): RagPage
    {
        return RagPage::create($data);
    }
}
