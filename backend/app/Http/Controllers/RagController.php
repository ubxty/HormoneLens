<?php

namespace App\Http\Controllers;

use App\Http\Requests\RagQueryRequest;
use App\Http\Resources\RagAnswerResource;
use App\Services\AI\BedrockService;
use App\Services\AI\PromptTemplates;
use App\Services\Rag\RagSearchService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RagController extends Controller
{
    public function __construct(
        private readonly RagSearchService $ragSearch,
        private readonly BedrockService $bedrock,
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

    /**
     * Stream a RAG answer via Server-Sent Events.
     */
    public function stream(RagQueryRequest $request): StreamedResponse
    {
        $question = $request->validated('question');
        $diseaseContext = $request->validated('disease_context');

        // First get the RAG search result (non-streamed)
        $result = $this->ragSearch->searchAndLog(
            question: $question,
            diseaseContext: $diseaseContext,
            userId: $request->user()->id,
        );

        $systemPrompt = PromptTemplates::ragSynthesis();
        $userMessage = "Knowledge Base:\n" . ($result['answer'] ?? '') . "\n\nQuestion: " . $question;

        return response()->stream(function () use ($systemPrompt, $userMessage, $result) {
            // Send metadata first
            echo "data: " . json_encode(['type' => 'meta', 'confidence' => $result['confidence'], 'sources' => count($result['source_pages'] ?? [])]) . "\n\n";
            ob_flush();
            flush();

            // Stream AI response via onChunk callback
            $failed = false;
            try {
                $this->bedrock->stream($systemPrompt, $userMessage, function (string $chunk) {
                    echo "data: " . json_encode(['type' => 'chunk', 'text' => $chunk]) . "\n\n";
                    ob_flush();
                    flush();
                });
            } catch (\Throwable $e) {
                $failed = true;
            }

            if ($failed) {
                echo "data: " . json_encode(['type' => 'chunk', 'text' => $result['answer'] ?? 'No answer available.']) . "\n\n";
            }

            echo "data: " . json_encode(['type' => 'done']) . "\n\n";
            ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
