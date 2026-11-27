<?php

declare(strict_types=1);

namespace App\Actions\Keys;

use App\Models\PublicKeyMaterial;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class PublishKeyAction
{
    public function execute(User $user, string $publicPayload): PublicKeyMaterial
    {
        return DB::transaction(function () use ($user, $publicPayload): PublicKeyMaterial {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            return $locked->publicKeys()->create(['public_payload' => base64_decode($publicPayload, true), 'published_at' => now()]);
        }, 3);
    }
}
