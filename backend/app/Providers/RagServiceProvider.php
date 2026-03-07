<?php

namespace App\Providers;

use App\Contracts\RagSearchInterface;
use App\Contracts\RagTraversalInterface;
use App\Services\AI\BedrockService;
use App\Services\Rag\RagSearchService;
use App\Services\Rag\RagTraversalEngine;
use Illuminate\Support\ServiceProvider;

class RagServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RagSearchInterface::class, RagSearchService::class);
        $this->app->bind(RagTraversalInterface::class, RagTraversalEngine::class);
        $this->app->singleton(BedrockService::class);
    }

    public function boot(): void
    {
        // Override Bedrock config with admin-stored credentials (if set)
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('ai_settings')) {
                $key = \App\Models\AiSetting::getValue('bedrock_aws_key');
                $secret = \App\Models\AiSetting::getValue('bedrock_aws_secret');
                $region = \App\Models\AiSetting::getValue('bedrock_region');

                if ($key) {
                    // Bearer mode (ABSK tokens) doesn't need a secret — use a
                    // placeholder so the vendor package's isConfigured() check passes.
                    $isBearerMode = str_starts_with($key, 'ABSK');
                    $effectiveSecret = $isBearerMode ? 'bearer-mode' : $secret;

                    if ($isBearerMode || $effectiveSecret) {
                        config([
                            'bedrock.connections.default.keys' => [[
                                'label'      => 'Admin-configured',
                                'aws_key'    => $key,
                                'aws_secret' => $effectiveSecret,
                                'region'     => $region ?: config('bedrock.connections.default.keys.0.region', 'us-east-1'),
                            ]],
                        ]);
                    }
                }

                // Load model alias overrides
                foreach (['default', 'smart', 'fast'] as $alias) {
                    $modelId = \App\Models\AiSetting::getValue("bedrock_model_{$alias}");
                    if ($modelId) {
                        config(["bedrock.aliases.{$alias}" => $modelId]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // DB not available yet (migrations running, etc.)
        }
    }
}
