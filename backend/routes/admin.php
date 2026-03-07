<?php

use App\Http\Controllers\Web\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Web\Admin\PageController as AdminPageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Web Routes
|--------------------------------------------------------------------------
| These routes are loaded with the 'web' middleware group and prefixed
| with '/admin'. All admin pages use a separate 'admin' auth guard
| backed by the super_admins table.
|--------------------------------------------------------------------------
*/

// ─── Admin Auth (guest:admin) ────────────────────────
Route::middleware('guest:admin')->group(function () {
    Route::get('/login',  [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
});
Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('auth:admin')->name('admin.logout');

// ─── Admin Pages (auth:admin) ────────────────────────
Route::middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('/',                   [AdminPageController::class, 'dashboard'])->name('dashboard');
    Route::get('/users',              [AdminPageController::class, 'users'])->name('users');
    Route::get('/users/{id}',         [AdminPageController::class, 'userShow'])->name('users.show');
    Route::get('/risk-analysis',      [AdminPageController::class, 'riskAnalysis'])->name('risk-analysis');
    Route::get('/simulations',        [AdminPageController::class, 'simulations'])->name('simulations');
    Route::get('/alerts',             [AdminPageController::class, 'alerts'])->name('alerts');
    Route::get('/reports',            [AdminPageController::class, 'reports'])->name('reports');
    Route::get('/rag',                [AdminPageController::class, 'rag'])->name('rag');
    Route::get('/rag/documents/{id}', [AdminPageController::class, 'ragDocument'])->name('rag.document');
    Route::get('/bedrock',            [AdminPageController::class, 'bedrock'])->name('bedrock');
    Route::get('/bedrock/models',     [AdminPageController::class, 'bedrockModels'])->name('bedrock.models');
    Route::get('/bedrock/usage',      [AdminPageController::class, 'bedrockUsage'])->name('bedrock.usage');
});
