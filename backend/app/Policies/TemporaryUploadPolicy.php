<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class TemporaryUploadPolicy
{
    public function delete(User $user, TemporaryUpload $temporaryUpload): Response
    {
        return $temporaryUpload->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound('Upload não encontrado.');
    }
}
