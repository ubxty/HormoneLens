<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DigitalTwinController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\FoodCompareController;
use App\Http\Controllers\FoodImpactController;
use App\Http\Controllers\HealthProfileController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\RagController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\SimulationCompareController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\SimulationSessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', RegisterController::class);
    Route::post('/login', LoginController::class);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // ── Auth ──────────────────────────────────────────
    Route::post('/logout', LogoutController::class);
    Route::get('/user', fn (\Illuminate\Http\Request $r) => new \App\Http\Resources\UserResource($r->user()));

    // ── Health Profile ───────────────────────────────
    // primary routes use hyphen, but frontend historically requested underscore
    Route::prefix('health-profile')->group(function () {
        Route::get('/', [HealthProfileController::class, 'show']);
        Route::post('/', [HealthProfileController::class, 'store']);
        Route::put('/', [HealthProfileController::class, 'update']);
    });

    // backward‑compatible aliases with underscore
    Route::prefix('health_profile')->group(function () {
        Route::get('/', [HealthProfileController::class, 'show']);
        Route::post('/', [HealthProfileController::class, 'store']);
        Route::put('/', [HealthProfileController::class, 'update']);
    });

    // ── Disease Data (dynamic) ────────────────────────
    Route::prefix('diseases')->group(function () {
        Route::get('/',         [DiseaseController::class, 'index']);
        Route::get('/my-data',  [DiseaseController::class, 'myData']);
        Route::get('/{slug}',   [DiseaseController::class, 'show']);
        Route::post('/{slug}',  [DiseaseController::class, 'store']);
    });

    // ── Digital Twin ─────────────────────────────────
    Route::prefix('digital-twin')->group(function () {
        Route::post('/generate', [DigitalTwinController::class, 'generate']);
        Route::get('/active', [DigitalTwinController::class, 'active']);
        Route::get('/', [DigitalTwinController::class, 'index']);
        Route::get('/{id}', [DigitalTwinController::class, 'show']);
    });

    // ── Simulations ──────────────────────────────────
    Route::prefix('simulations')->group(function () {
        Route::post('/run', [SimulationController::class, 'run']);
        Route::post('/chain', [SimulationSessionController::class, 'chain']);
        Route::post('/compare', SimulationCompareController::class);
        Route::get('/chain/{id}', [SimulationSessionController::class, 'chain_history']);
        Route::get('/', [SimulationController::class, 'index']);
        Route::get('/{id}', [SimulationController::class, 'show']);
    });

    // ── Food Impact ──────────────────────────────────
    Route::post('/food-impact', FoodImpactController::class);
    Route::post('/food-compare', FoodCompareController::class);

    // ── Alerts ───────────────────────────────────────
    Route::prefix('alerts')->group(function () {
        Route::get('/', [AlertController::class, 'index']);
        Route::get('/unread-count', [AlertController::class, 'unreadCount']);
        Route::patch('/read-all', [AlertController::class, 'markAllRead']);
        Route::patch('/{id}/read', [AlertController::class, 'markRead']);
    });

    // ── History ──────────────────────────────────────
    Route::prefix('history')->group(function () {
        Route::get('/', [HistoryController::class, 'index']);
        Route::get('/{id}', [HistoryController::class, 'show']);
        Route::post('/{id}/rerun', [HistoryController::class, 'rerun']);
        Route::delete('/{id}', [HistoryController::class, 'destroy']);
    });

    // ── RAG Knowledge Base ───────────────────────────
    Route::middleware('throttle:rag')->post('/rag/query', RagController::class);
    Route::middleware('throttle:rag')->post('/rag/query-stream', [RagController::class, 'stream']);

    // ── Predictions ──────────────────────────────────
    Route::prefix('predictions')->group(function () {
        Route::get('/', [PredictionController::class, 'all']);
        Route::get('/cortisol', [PredictionController::class, 'cortisol']);
        Route::get('/androgen', [PredictionController::class, 'androgen']);
        Route::get('/cycle', [PredictionController::class, 'cycle']);
        Route::get('/hba1c', [PredictionController::class, 'hba1c']);
        Route::get('/long-term', [PredictionController::class, 'longTerm']);
    });
});
