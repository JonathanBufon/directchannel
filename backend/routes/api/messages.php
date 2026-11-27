<?php

declare(strict_types=1);

use App\Http\Controllers\Api\MessageController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'index'])->whereNumber('conversation');
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store'])->whereNumber('conversation');
    Route::post('/messages/{message}/delivered', [MessageController::class, 'delivered'])->whereNumber('message');
    Route::post('/messages/{message}/read', [MessageController::class, 'read'])->whereNumber('message');
    Route::patch('/messages/{message}', [MessageController::class, 'future'])->whereNumber('message');
    Route::delete('/messages/{message}', [MessageController::class, 'future'])->whereNumber('message');
});
