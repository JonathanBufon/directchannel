<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

final class UserPolicy
{
    public function view(User $viewer, User $user): Response
    {
        return ! $user->trashed() && ! $user->blocksMade()->where('blocked_user_id', $viewer->id)->exists()
            ? Response::allow()
            : Response::denyAsNotFound('Usuário não encontrado.');
    }
}
