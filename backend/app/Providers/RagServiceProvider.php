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
        //
    }
}
