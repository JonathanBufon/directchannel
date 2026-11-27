<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Files\DeleteTemporaryUploadAction;
use App\Actions\Files\StoreTemporaryUploadAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Files\UploadRequest;
use App\Http\Resources\TemporaryUploadResource;
use App\Models\Conversation;
use App\Models\TemporaryUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

final class UploadController extends Controller
{
    public function store(UploadRequest $request, Conversation $conversation, StoreTemporaryUploadAction $action): TemporaryUploadResource
    {
        return new TemporaryUploadResource($action->execute($request->user(), $conversation, $request->file('file')));
    }

    public function destroy(TemporaryUpload $temporaryUpload, DeleteTemporaryUploadAction $action): JsonResponse
    {
        Gate::authorize('delete', $temporaryUpload);
        $action->execute($temporaryUpload);

        return response()->json(['data' => ['message' => 'Upload temporário removido.']]);
    }
}
