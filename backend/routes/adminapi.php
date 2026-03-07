<?php

use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\Auth\LogoutController as AdminLogoutController;
use App\Http\Controllers\Admin\AlertManagementController;
use App\Http\Controllers\Admin\BedrockManagementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RagManagementController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SimulationLogController;
use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
| These routes are loaded with the 'api' middleware group and prefixed
| with '/api/admin'. Authentication uses Sanctum tokens issued from the
| super_admins table via the 'admin' guard.
|--------------------------------------------------------------------------
*/

// ─── Public admin API auth ───────────────────────────
Route::middleware('throttle:auth')->group(function () {
    Route::post('/login', AdminLoginController::class);
});

// ─── Authenticated admin API ─────────────────────────
Route::middleware(['auth:sanctum', 'superadmin'])->group(function () {
    Route::post('/logout', AdminLogoutController::class);

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
        Route::get('/documents', [RagManagementController::class, 'documents']);
        Route::post('/documents', [RagManagementController::class, 'storeDocument']);
        Route::get('/documents/{id}', [RagManagementController::class, 'showDocument']);
        Route::put('/documents/{id}', [RagManagementController::class, 'updateDocument']);
        Route::delete('/documents/{id}', [RagManagementController::class, 'destroyDocument']);
        Route::post('/nodes', [RagManagementController::class, 'storeNode']);
        Route::put('/nodes/{id}', [RagManagementController::class, 'updateNode']);
        Route::delete('/nodes/{id}', [RagManagementController::class, 'destroyNode']);
        Route::post('/pages', [RagManagementController::class, 'storePage']);
        Route::put('/pages/{id}', [RagManagementController::class, 'updatePage']);
        Route::delete('/pages/{id}', [RagManagementController::class, 'destroyPage']);
    });

    // ── Bedrock AI Management ────────────────────
    Route::prefix('bedrock')->group(function () {
        Route::get('/status', [BedrockManagementController::class, 'status']);
        Route::get('/models', [BedrockManagementController::class, 'models']);
        Route::get('/usage', [BedrockManagementController::class, 'usage']);
        Route::get('/pricing', [BedrockManagementController::class, 'pricing']);
        Route::post('/test', [BedrockManagementController::class, 'test']);
        Route::get('/settings', [BedrockManagementController::class, 'settings']);
        Route::put('/settings', [BedrockManagementController::class, 'updateSettings']);
    });
});
