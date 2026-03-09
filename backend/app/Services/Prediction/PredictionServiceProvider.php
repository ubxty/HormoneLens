<?php

namespace App\Services\Prediction;

use Illuminate\Support\ServiceProvider;

class PredictionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CortisolPredictionService::class);
        $this->app->singleton(AndrogenPredictionService::class);
        $this->app->singleton(CyclePredictionService::class);
        $this->app->singleton(HbA1cPredictionService::class);
        $this->app->singleton(LongTermProjectionService::class, function ($app) {
            return new LongTermProjectionService(
                $app->make(CortisolPredictionService::class),
                $app->make(AndrogenPredictionService::class),
                $app->make(CyclePredictionService::class),
                $app->make(HbA1cPredictionService::class),
            );
        });
    }
}
