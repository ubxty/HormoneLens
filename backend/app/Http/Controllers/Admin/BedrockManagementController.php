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

    /**
     * Get current credentials (masked).
     */
    public function credentials(): JsonResponse
    {
        return response()->json([
            'aws_key' => $this->maskValue(AiSetting::getValue('bedrock_aws_key', config('bedrock.connections.default.keys.0.aws_key', ''))),
            'aws_secret' => $this->maskValue(AiSetting::getValue('bedrock_aws_secret', config('bedrock.connections.default.keys.0.aws_secret', ''))),
            'region' => AiSetting::getValue('bedrock_region', config('bedrock.connections.default.keys.0.region', 'us-east-1')),
            'has_keys' => !empty(AiSetting::getValue('bedrock_aws_key', config('bedrock.connections.default.keys.0.aws_key', ''))),
        ]);
    }

    /**
     * Update AWS credentials.
     */
    public function updateCredentials(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'aws_key' => 'required|string|min:16|max:128',
            'aws_secret' => 'required|string|min:16|max:128',
            'region' => 'required|string|max:30',
        ]);

        AiSetting::setValue('bedrock_aws_key', $validated['aws_key'], 'string', 'credentials', 'AWS Access Key ID');
        AiSetting::setValue('bedrock_aws_secret', $validated['aws_secret'], 'string', 'credentials', 'AWS Secret Access Key');
        AiSetting::setValue('bedrock_region', $validated['region'], 'string', 'credentials', 'AWS Region');

        // Update runtime config so connection test works immediately
        config([
            'bedrock.connections.default.keys' => [[
                'label' => 'Admin-configured',
                'aws_key' => $validated['aws_key'],
                'aws_secret' => $validated['aws_secret'],
                'region' => $validated['region'],
            ]],
        ]);

        return response()->json(['message' => 'Credentials updated successfully']);
    }

    private function maskValue(string $value): string
    {
        if (empty($value) || strlen($value) < 8) {
            return $value ? '••••••••' : '';
        }

        return substr($value, 0, 4) . str_repeat('•', strlen($value) - 8) . substr($value, -4);
    }
}
