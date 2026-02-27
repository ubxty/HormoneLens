<?php

namespace App\Repositories\Rag;

use App\Models\RagDocument;
use Illuminate\Database\Eloquent\Collection;

class RagDocumentRepository
{
    public function all(): Collection
    {
        return RagDocument::all();
    }

    public function create(array $data): RagDocument
    {
        return RagDocument::create($data);
    }
}
