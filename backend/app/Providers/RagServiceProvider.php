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

                if ($key && $secret) {
                    config([
                        'bedrock.connections.default.keys' => [[
                            'label'      => 'Admin-configured',
                            'aws_key'    => $key,
                            'aws_secret' => $secret,
                            'region'     => $region ?: config('bedrock.connections.default.keys.0.region', 'us-east-1'),
                        ]],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // DB not available yet (migrations running, etc.)
        }
    }
}
