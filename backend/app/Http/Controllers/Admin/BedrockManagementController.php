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
        $configured = $this->bedrock->isConfigured();

        return response()->json([
            'configured' => $configured,
            'available'  => $configured, // actual connectivity verified via test button
            'settings'   => AiSetting::all()->groupBy('group'),
        ]);
    }

    public function models(): JsonResponse
    {
        try {
            $models = $this->bedrock->listModels();

            return response()->json([
                'success' => true,
                'models'  => $models,
                'count'   => count($models),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'models'  => [],
                'count'   => 0,
                'error'   => $this->friendlyError($e),
            ], 200); // 200 so JS can parse the response
        }
    }

    public function usage(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data'    => $this->bedrock->getUsage(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'data'    => [],
                'error'   => $this->friendlyError($e),
            ]);
        }
    }

    public function pricing(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data'    => $this->bedrock->getPricing(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'data'    => [],
                'error'   => $this->friendlyError($e),
            ]);
        }
    }

    public function test(Request $request): JsonResponse
    {
        try {
            $result = $this->bedrock->testConnection();

            return response()->json([
                'success'       => $result['success'] ?? false,
                'message'       => $result['message'] ?? 'Unknown result',
                'response_time' => $result['response_time'] ?? null,
                'model_count'   => $result['model_count'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $this->friendlyError($e),
            ]);
        }
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
        $storedKey = AiSetting::getValue('bedrock_aws_key', config('bedrock.connections.default.keys.0.aws_key', ''));
        $isBearerMode = str_starts_with($storedKey, 'ABSK');

        return response()->json([
            'auth_mode'    => $isBearerMode ? 'bearer' : 'keys',
            'bearer_token' => $isBearerMode ? $this->maskValue($storedKey) : '',
            'aws_key'      => !$isBearerMode ? $this->maskValue($storedKey) : '',
            'aws_secret'   => !$isBearerMode ? $this->maskValue(AiSetting::getValue('bedrock_aws_secret', config('bedrock.connections.default.keys.0.aws_secret', ''))) : '',
            'region'       => AiSetting::getValue('bedrock_region', config('bedrock.connections.default.keys.0.region', 'us-east-1')),
            'has_keys'     => !empty($storedKey),
        ]);
    }

    /**
     * Update AWS credentials.
     * Supports two modes:
     *   - bearer: a single ABSK... bearer token (no secret needed)
     *   - keys:   traditional IAM Access Key ID + Secret Access Key
     */
    public function updateCredentials(Request $request): JsonResponse
    {
        $mode = $request->input('auth_mode', 'keys');

        if ($mode === 'bearer') {
            $validated = $request->validate([
                'bearer_token' => ['required', 'string', 'min:10', 'max:512', 'regex:/^ABSK/'],
                'region'       => 'required|string|max:30',
            ]);

            $awsKey    = $validated['bearer_token'];
            $awsSecret = 'bearer-mode'; // placeholder so vendor isConfigured() passes
        } else {
            $validated = $request->validate([
                'aws_key'    => 'required|string|min:16|max:128',
                'aws_secret' => 'required|string|min:16|max:128',
                'region'     => 'required|string|max:30',
            ]);

            $awsKey    = $validated['aws_key'];
            $awsSecret = $validated['aws_secret'];
        }

        $region = $validated['region'];

        AiSetting::setValue('bedrock_aws_key', $awsKey, 'string', 'credentials', 'AWS Credential Key');
        AiSetting::setValue('bedrock_aws_secret', $awsSecret, 'string', 'credentials', 'AWS Secret / Bearer Token');
        AiSetting::setValue('bedrock_region', $region, 'string', 'credentials', 'AWS Region');

        // Update runtime config so the connection test works immediately without a restart
        config([
            'bedrock.connections.default.keys' => [[
                'label'      => 'Admin-configured',
                'aws_key'    => $awsKey,
                'aws_secret' => $awsSecret,
                'region'     => $region,
            ]],
        ]);

        return response()->json(['message' => 'Credentials saved successfully']);
    }

    private function maskValue(string $value): string
    {
        if (empty($value) || strlen($value) < 8) {
            return $value ? '••••••••' : '';
        }

        return substr($value, 0, 4) . str_repeat('•', strlen($value) - 8) . substr($value, -4);
    }

    /**
     * Get current model alias configuration (default, smart, fast).
     */
    public function modelAliases(): JsonResponse
    {
        return response()->json([
            'default' => AiSetting::getValue('bedrock_model_default', config('bedrock.aliases.default', '')),
            'smart'   => AiSetting::getValue('bedrock_model_smart', config('bedrock.aliases.smart', '')),
            'fast'    => AiSetting::getValue('bedrock_model_fast', config('bedrock.aliases.fast', '')),
        ]);
    }

    /**
     * Update model alias configuration.
     */
    public function updateModelAliases(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'default' => 'nullable|string|max:200',
            'smart'   => 'nullable|string|max:200',
            'fast'    => 'nullable|string|max:200',
        ]);

        foreach (['default', 'smart', 'fast'] as $alias) {
            if (isset($validated[$alias])) {
                AiSetting::setValue(
                    "bedrock_model_{$alias}",
                    $validated[$alias],
                    'string',
                    'models',
                    ucfirst($alias) . ' model alias'
                );

                // Also update runtime config
                config(["bedrock.aliases.{$alias}" => $validated[$alias]]);
            }
        }

        return response()->json(['message' => 'Model aliases updated']);
    }

    private function friendlyError(\Throwable $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'Could not resolve host')) {
            return 'Cannot reach AWS Bedrock — check your region and network connectivity.';
        }
        if (str_contains($message, '403') || str_contains($message, 'Forbidden') || str_contains($message, 'AccessDenied')) {
            return 'Access denied — your credentials may be invalid or lack Bedrock permissions.';
        }
        if (str_contains($message, '401') || str_contains($message, 'Unauthorized')) {
            return 'Authentication failed — your API key or bearer token is invalid or expired.';
        }
        if (str_contains($message, 'No AWS credential keys configured') || str_contains($message, 'not configured')) {
            return 'Bedrock is not configured — please save your API credentials first.';
        }
        if (str_contains($message, 'cURL error') || str_contains($message, 'Connection refused')) {
            return 'Network error — cannot connect to AWS. Check your internet connection.';
        }

        return $message ?: 'An unexpected error occurred.';
    }
}
