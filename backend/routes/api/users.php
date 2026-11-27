<?php

declare(strict_types=1);

use App\Http\Controllers\Api\BlocksController;
use App\Http\Controllers\Api\KeysController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\PrivacyController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\UsersController;
use Illuminate\Support\Facades\Route;

Route::post('/password/forgot', [PasswordController::class, 'forgot'])->middleware('throttle:6,1');
Route::post('/password/reset', [PasswordController::class, 'reset'])->middleware('throttle:6,1');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/users', [UsersController::class, 'index']);
    Route::get('/users/{user}', [UsersController::class, 'show']);
    Route::patch('/me', [ProfileController::class, 'update']);
    Route::patch('/me/password', [ProfileController::class, 'password']);
    Route::delete('/me', [ProfileController::class, 'destroy']);
    Route::get('/me/privacy', [PrivacyController::class, 'show']);
    Route::patch('/me/privacy', [PrivacyController::class, 'update']);
    Route::get('/blocks', [BlocksController::class, 'index']);
    Route::post('/blocks', [BlocksController::class, 'store']);
    Route::delete('/blocks/{user}', [BlocksController::class, 'destroy'])->withTrashed();
    Route::post('/me/keys', [KeysController::class, 'store']);
    Route::get('/users/{user}/keys/current', [KeysController::class, 'current']);
});
