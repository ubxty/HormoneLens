<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DigitalTwinController;
use App\Http\Controllers\DiseaseDiabetesController;
use App\Http\Controllers\DiseasePcodController;
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
Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class);

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
    Route::prefix('health-profile')->group(function () {
        Route::get('/', [HealthProfileController::class, 'show']);
        Route::post('/', [HealthProfileController::class, 'store']);
        Route::put('/', [HealthProfileController::class, 'update']);
    });

    // ── Disease Data ─────────────────────────────────
    Route::prefix('disease')->group(function () {
        Route::get('/diabetes', [DiseaseDiabetesController::class, 'show']);
        Route::post('/diabetes', [DiseaseDiabetesController::class, 'store']);
        Route::get('/pcod', [DiseasePcodController::class, 'show']);
        Route::post('/pcod', [DiseasePcodController::class, 'store']);
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
        Route::patch('/{id}/read', [AlertController::class, 'markRead']);
    });

    // ── History ──────────────────────────────────────
    Route::prefix('history')->group(function () {
        Route::get('/', [HistoryController::class, 'index']);
        Route::get('/{id}', [HistoryController::class, 'show']);
        Route::post('/{id}/rerun', [HistoryController::class, 'rerun']);
    });

    // ── RAG Knowledge Base ───────────────────────────
    Route::post('/rag/query', RagController::class);

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
    });
});
