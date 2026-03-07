<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DigitalTwinController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\FoodImpactController;
use App\Http\Controllers\HealthProfileController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\RagController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\Admin\AlertManagementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SimulationLogController;
use App\Http\Controllers\Admin\UserManagementController;
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
        Route::get('/', [SimulationController::class, 'index']);
        Route::get('/{id}', [SimulationController::class, 'show']);
    });

    // ── Food Impact ──────────────────────────────────
    Route::post('/food-impact', FoodImpactController::class);

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

    /*
    |----------------------------------------------------------------------
    | Admin Routes
    |----------------------------------------------------------------------
    */
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', DashboardController::class);

        Route::get('/users', [UserManagementController::class, 'index']);
        Route::get('/users/{id}', [UserManagementController::class, 'show']);
        Route::patch('/users/{id}/toggle-admin', [UserManagementController::class, 'toggleAdmin']);

        Route::get('/simulations', [SimulationLogController::class, 'index']);
        Route::get('/simulations/{id}', [SimulationLogController::class, 'show']);

        Route::get('/alerts', [AlertManagementController::class, 'index']);
        Route::get('/alerts/{id}', [AlertManagementController::class, 'show']);

        Route::get('/reports', ReportController::class);

        // ── RAG Knowledge Base CRUD ──────────────────
        Route::prefix('rag')->group(function () {
            Route::get('/documents', [\App\Http\Controllers\Admin\RagManagementController::class, 'documents']);
            Route::post('/documents', [\App\Http\Controllers\Admin\RagManagementController::class, 'storeDocument']);
            Route::get('/documents/{id}', [\App\Http\Controllers\Admin\RagManagementController::class, 'showDocument']);
            Route::put('/documents/{id}', [\App\Http\Controllers\Admin\RagManagementController::class, 'updateDocument']);
            Route::delete('/documents/{id}', [\App\Http\Controllers\Admin\RagManagementController::class, 'destroyDocument']);
            Route::post('/nodes', [\App\Http\Controllers\Admin\RagManagementController::class, 'storeNode']);
            Route::put('/nodes/{id}', [\App\Http\Controllers\Admin\RagManagementController::class, 'updateNode']);
            Route::delete('/nodes/{id}', [\App\Http\Controllers\Admin\RagManagementController::class, 'destroyNode']);
            Route::post('/pages', [\App\Http\Controllers\Admin\RagManagementController::class, 'storePage']);
            Route::put('/pages/{id}', [\App\Http\Controllers\Admin\RagManagementController::class, 'updatePage']);
            Route::delete('/pages/{id}', [\App\Http\Controllers\Admin\RagManagementController::class, 'destroyPage']);
        });

        // ── Bedrock AI Management ────────────────────
        Route::prefix('bedrock')->group(function () {
            Route::get('/status', [\App\Http\Controllers\Admin\BedrockManagementController::class, 'status']);
            Route::get('/models', [\App\Http\Controllers\Admin\BedrockManagementController::class, 'models']);
            Route::get('/usage', [\App\Http\Controllers\Admin\BedrockManagementController::class, 'usage']);
            Route::get('/pricing', [\App\Http\Controllers\Admin\BedrockManagementController::class, 'pricing']);
            Route::post('/test', [\App\Http\Controllers\Admin\BedrockManagementController::class, 'test']);
            Route::get('/settings', [\App\Http\Controllers\Admin\BedrockManagementController::class, 'settings']);
            Route::put('/settings', [\App\Http\Controllers\Admin\BedrockManagementController::class, 'updateSettings']);
        });
    });
});
