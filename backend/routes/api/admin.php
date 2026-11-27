<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminReportController;
use App\Http\Controllers\Api\AdminSettingsController;
use App\Http\Controllers\Api\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth:sanctum', 'can:admin'])->group(function (): void {
    Route::get('/dashboard', AdminDashboardController::class);
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->withTrashed();
    Route::post('/users/{user}/block', [AdminUserController::class, 'block'])->withTrashed();
    Route::post('/users/{user}/unblock', [AdminUserController::class, 'unblock'])->withTrashed();
    Route::get('/reports', [AdminReportController::class, 'index']);
    Route::get('/reports/{report}', [AdminReportController::class, 'show']);
    Route::post('/reports/{report}/conclude', [AdminReportController::class, 'conclude']);
    Route::delete('/reports/{report}', [AdminReportController::class, 'destroy']);
    Route::get('/settings', [AdminSettingsController::class, 'show']);
    Route::patch('/settings', [AdminSettingsController::class, 'update']);
});
