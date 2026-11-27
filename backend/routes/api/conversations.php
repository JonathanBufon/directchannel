<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ConversationsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/conversations', [ConversationsController::class, 'index']);
    Route::post('/conversations', [ConversationsController::class, 'store']);
    Route::get('/conversations/{conversation}', [ConversationsController::class, 'show']);
    Route::delete('/conversations/{conversation}', [ConversationsController::class, 'destroy']);
});
