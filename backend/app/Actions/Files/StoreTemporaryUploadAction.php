<?php

declare(strict_types=1);

namespace App\Actions\Files;

use App\Models\AccessGrantToken;
use App\Models\Conversation;
use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class StoreTemporaryUploadAction
{
    public function execute(User $user, Conversation $conversation, UploadedFile $file): TemporaryUpload
    {
        $path = $file->storeAs('uploads/'.$user->id, (string) Str::uuid(), 'local');
        if ($path === false) {
            throw new RuntimeException('Não foi possível armazenar o arquivo.');
        }

        try {
            return TemporaryUpload::create([
                'user_id' => $user->id,
                'access_grant_id' => AccessGrantToken::findOrFail($user->currentAccessToken()->getKey())->access_grant_id,
                'conversation_id' => $conversation->id,
                'storage_key' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
                'expires_at' => now()->addHours(24),
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }
    }
}
