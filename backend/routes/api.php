<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', [AuthController::class, 'me']);
});

require __DIR__.'/api/users.php';
require __DIR__.'/api/conversations.php';
require __DIR__.'/api/messages.php';
require __DIR__.'/api/groups.php';
require __DIR__.'/api/files.php';
require __DIR__.'/api/reports.php';
require __DIR__.'/api/admin.php';
