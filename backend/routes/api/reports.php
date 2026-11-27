<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports', [ReportController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/reports/{report}', [ReportController::class, 'show']);
});
