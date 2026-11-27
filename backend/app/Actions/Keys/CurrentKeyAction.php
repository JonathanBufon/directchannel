<?php

declare(strict_types=1);

namespace App\Actions\Keys;

use App\Models\PublicKeyMaterial;
use App\Models\User;

final class CurrentKeyAction
{
    public function execute(User $user): PublicKeyMaterial
    {
        return $user->publicKeys()->orderByDesc('published_at')->orderByDesc('id')->firstOrFail();
    }
}
