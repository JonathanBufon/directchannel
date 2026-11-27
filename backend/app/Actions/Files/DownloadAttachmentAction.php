<?php

declare(strict_types=1);

namespace App\Actions\Files;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DownloadAttachmentAction
{
    public function execute(Attachment $attachment): StreamedResponse
    {
        $disk = Storage::disk('local');
        abort_unless($disk->exists($attachment->storage_key), 404, 'Arquivo não encontrado.');

        return $disk->download($attachment->storage_key, 'anexo-'.$attachment->id, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
