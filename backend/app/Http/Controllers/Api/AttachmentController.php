<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Files\DownloadAttachmentAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttachmentResource;
use App\Models\Attachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AttachmentController extends Controller
{
    public function show(Attachment $attachment): AttachmentResource
    {
        Gate::authorize('view', $attachment);

        return new AttachmentResource($attachment);
    }

    public function download(Attachment $attachment, DownloadAttachmentAction $action): StreamedResponse
    {
        Gate::authorize('view', $attachment);

        return $action->execute($attachment);
    }

    public function update(Attachment $attachment): JsonResponse
    {
        Gate::authorize('update', $attachment);

        return response()->json(['message' => 'A edição de metadados de anexos está prevista para uma fase futura.'], 501);
    }

    public function destroy(Attachment $attachment): JsonResponse
    {
        Gate::authorize('delete', $attachment);

        return response()->json(['message' => 'A remoção de anexos está prevista para uma fase futura.'], 501);
    }
}
