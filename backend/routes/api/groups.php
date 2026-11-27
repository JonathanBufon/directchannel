<?php

declare(strict_types=1);

use App\Http\Controllers\Api\GroupController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/groups', [GroupController::class, 'index']);
    Route::post('/groups', [GroupController::class, 'store']);
    Route::get('/groups/{group}', [GroupController::class, 'show'])->whereNumber('group');
    Route::patch('/groups/{group}', [GroupController::class, 'update'])->whereNumber('group');
    Route::post('/groups/{group}/members', [GroupController::class, 'addMembers'])->whereNumber('group');
    Route::delete('/groups/{group}/members/{user}', [GroupController::class, 'removeMember'])->whereNumber(['group', 'user']);
    Route::post('/groups/{group}/responsibles/{user}', [GroupController::class, 'promote'])->whereNumber(['group', 'user']);
    Route::delete('/groups/{group}/responsibles/{user}', [GroupController::class, 'demote'])->whereNumber(['group', 'user']);
    Route::post('/groups/{group}/leave', [GroupController::class, 'leave'])->whereNumber('group');
    Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->whereNumber('group');
});
