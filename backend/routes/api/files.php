<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/conversations/{conversation}/uploads', [UploadController::class, 'store']);
    Route::delete('/uploads/{temporaryUpload}', [UploadController::class, 'destroy']);
    Route::get('/attachments/{attachment}', [AttachmentController::class, 'show']);
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download']);
    Route::patch('/attachments/{attachment}', [AttachmentController::class, 'update']);
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy']);
});
