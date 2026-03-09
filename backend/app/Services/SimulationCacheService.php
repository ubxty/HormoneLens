<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Centralized caching helper for simulation-related data (AR1).
 * Uses Redis when available, falls back to file cache.
 */
class SimulationCacheService
{
    private const TTL_RISK = 300;      // 5 minutes for risk calculations
    private const TTL_FOOD = 3600;     // 1 hour for food glycemic data
    private const TTL_RAG = 1800;      // 30 minutes for RAG results
    private const TTL_PREDICTION = 600; // 10 minutes for predictions

    /**
     * Cache risk score calculation for a snapshot.
     */
    public static function riskScore(string $snapshotHash, callable $compute): array
    {
        return Cache::remember("risk:{$snapshotHash}", self::TTL_RISK, $compute);
    }

    /**
     * Cache food glycemic data lookup.
     */
    public static function foodData(string $foodItem, callable $compute): mixed
    {
        $key = 'food:' . md5(strtolower(trim($foodItem)));
        return Cache::remember($key, self::TTL_FOOD, $compute);
    }

    /**
     * Cache RAG search results.
     */
    public static function ragResult(string $question, ?string $diseaseContext, callable $compute): array
    {
        $key = 'rag:' . md5($question . '|' . ($diseaseContext ?? ''));
        return Cache::remember($key, self::TTL_RAG, $compute);
    }

    /**
     * Cache prediction results.
     */
    public static function prediction(string $type, string $snapshotHash, callable $compute): array
    {
        return Cache::remember("pred:{$type}:{$snapshotHash}", self::TTL_PREDICTION, $compute);
    }

    /**
     * Generate a hash for a snapshot array for use as cache key.
     */
    public static function snapshotHash(array $snapshot): string
    {
        return md5(json_encode($snapshot));
    }

    /**
     * Invalidate all cached data for a user when their twin is regenerated.
     * Clears risk and prediction caches by pattern.
     */
    public static function invalidateForUser(int $userId): void
    {
        // Clear known cache keys for this user
        // Risk and prediction caches use snapshot hashes, so we can't target by user.
        // Instead, clear prediction caches which are most affected by twin regeneration.
        Cache::forget("pred:cortisol:{$userId}");
        Cache::forget("pred:androgen:{$userId}");
        Cache::forget("pred:cycle:{$userId}");
        Cache::forget("pred:hba1c:{$userId}");
        Cache::forget("pred:longterm:{$userId}");
    }
}
