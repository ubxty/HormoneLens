<?php

namespace App\Http\Controllers;

use App\Http\Requests\RagQueryRequest;
use App\Http\Resources\RagAnswerResource;
use App\Services\Rag\RagSearchService;
use Illuminate\Http\Request;

class RagController extends Controller
{
    public function __construct(
        private readonly RagSearchService $ragSearch,
    ) {}

    /**
     * Query the RAG knowledge base.
     */
    public function __invoke(RagQueryRequest $request)
    {
        $result = $this->ragSearch->searchAndLog(
            question: $request->validated('question'),
            diseaseContext: $request->validated('disease_context'),
            userId: $request->user()->id,
        );

        return response()->json([
            'success' => true,
            'data' => new RagAnswerResource($result),
        ]);
    }
}
