<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnviChatController extends Controller
{
    private const MODEL = 'amazon.nova-pro-v1:0';

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $region = config('bedrock.connections.default.keys.0.region', env('BEDROCK_REGION', 'us-east-1'));
        $token  = env('BEDROCK_AWS_KEY');
        $url    = "https://bedrock-runtime.{$region}.amazonaws.com/model/" . self::MODEL . "/converse";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'X-Amz-Bedrock-Region' => $region,
            ])->post($url, [
                'messages' => [
                    [
                        'role'    => 'user',
                        'content' => [['text' => $request->input('message')]],
                    ],
                ],
                'system' => [
                    ['text' => $this->systemPrompt()],
                ],
                'inferenceConfig' => [
                    'maxTokens'   => 512,
                    'temperature' => 0.5,
                ],
            ]);

            if (! $response->successful()) {
                Log::error('Anvi Bedrock error', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['reply' => "I'm having a little trouble right now. Please try again in a moment!"]);
            }

            $text = $response->json('output.message.content.0.text') ?? '';

            return response()->json(['reply' => $text ?: "I'm not sure how to answer that. Try asking about hormones, PCOS, or metabolism!"]);

        } catch (\Throwable $e) {
            Log::error('Anvi chat error', ['message' => $e->getMessage()]);
            return response()->json(['reply' => "I'm having a little trouble right now. Please try again in a moment!"]);
        }
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are Anvi, the friendly AI health assistant for the HormoneLens platform.

Your role is to help users understand general health, hormones, metabolism, and lifestyle habits in a simple and friendly way.

You appear as a chatbot on the HormoneLens landing page to answer general health questions related to hormones, metabolism, PCOS, insulin resistance, thyroid health, stress, sleep, and metabolic wellness.

You are NOT a doctor and you do NOT provide medical diagnosis or treatment advice.

PERSONALITY: You are friendly, supportive, intelligent, calm, easy to understand, slightly conversational. Your tone feels human, warm, and trustworthy. Never sound robotic or overly technical.

TOPICS: PCOS, insulin resistance, Type 2 diabetes risk, thyroid imbalance, hormonal imbalance, metabolism and metabolic health, sleep and circadian rhythm, stress and cortisol, diet and blood sugar, exercise and metabolism, lifestyle and hormone balance, women's hormonal health.

BOUNDARIES:
- You do NOT diagnose medical conditions.
- You do NOT prescribe medications or treatments.
- You do NOT replace a doctor, endocrinologist, or dietitian.
- If a user asks for diagnosis or prescription advice, gently remind them to consult a qualified healthcare professional.
- Keep responses concise — 2 to 5 short paragraphs maximum.
- Use simple everyday language. Avoid heavy medical jargon.
- If a question is completely unrelated to health, politely redirect the user to HormoneLens health topics.
PROMPT;
    }
}
