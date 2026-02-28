<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\Admin\PageController as AdminPageController;
use Illuminate\Support\Facades\Route;

// ─── Landing page (public) ───────────────────────────
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('landing');
})->name('home');

// ─── Auth (guest) ────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ─── User pages (auth) ──────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/dashboard',      [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/health-profile', [PageController::class, 'healthProfile'])->name('health-profile');
    Route::get('/disease/{slug}',  [PageController::class, 'disease'])->name('disease.show');
    Route::get('/digital-twin',   [PageController::class, 'digitalTwin'])->name('digital-twin');
    Route::get('/simulations',    [PageController::class, 'simulations'])->name('simulations');
    Route::get('/food-impact',    [PageController::class, 'foodImpact'])->name('food-impact');
    Route::get('/alerts',         [PageController::class, 'alerts'])->name('alerts');
    Route::get('/history',        [PageController::class, 'history'])->name('history');
    Route::get('/knowledge',      [PageController::class, 'ragQuery'])->name('knowledge');
});

// ─── Admin pages (auth + admin) ─────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',                   [AdminPageController::class, 'dashboard'])->name('dashboard');
    Route::get('/users',              [AdminPageController::class, 'users'])->name('users');
    Route::get('/users/{id}',         [AdminPageController::class, 'userShow'])->name('users.show');
    Route::get('/risk-analysis',      [AdminPageController::class, 'riskAnalysis'])->name('risk-analysis');
    Route::get('/simulations',        [AdminPageController::class, 'simulations'])->name('simulations');
    Route::get('/alerts',             [AdminPageController::class, 'alerts'])->name('alerts');
    Route::get('/reports',            [AdminPageController::class, 'reports'])->name('reports');
    Route::get('/rag',                [AdminPageController::class, 'rag'])->name('rag');
    Route::get('/rag/documents/{id}', [AdminPageController::class, 'ragDocument'])->name('rag.document');
});
