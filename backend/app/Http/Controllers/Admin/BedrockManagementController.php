<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Services\AI\BedrockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BedrockManagementController extends Controller
{
    public function __construct(
        private readonly BedrockService $bedrock,
    ) {}

    public function status(): JsonResponse
    {
        return response()->json([
            'available' => $this->bedrock->isAvailable(),
            'settings' => AiSetting::all()->groupBy('group'),
        ]);
    }

    public function models(): JsonResponse
    {
        return response()->json($this->bedrock->listModels());
    }

    public function usage(): JsonResponse
    {
        return response()->json($this->bedrock->getUsage());
    }

    public function pricing(): JsonResponse
    {
        return response()->json($this->bedrock->getPricing());
    }

    public function test(Request $request): JsonResponse
    {
        $result = $this->bedrock->testConnection();

        return response()->json($result);
    }

    public function settings(): JsonResponse
    {
        return response()->json(AiSetting::all()->groupBy('group'));
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required|string',
        ]);

        foreach ($validated['settings'] as $setting) {
            AiSetting::where('key', $setting['key'])->update(['value' => $setting['value']]);
        }

        return response()->json(['message' => 'Settings updated']);
    }
}
