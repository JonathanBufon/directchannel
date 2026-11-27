<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class GroupPolicy
{
    public function view(User $user, Group $group): Response
    {
        return $group->conversation->activeParticipantFor($user) !== null
            ? Response::allow() : Response::denyAsNotFound('Grupo não encontrado.');
    }

    public function manage(User $user, Group $group): Response
    {
        $visibility = $this->view($user, $group);
        if ($visibility->denied()) {
            return $visibility;
        }

        return $group->isResponsible($user)
            ? Response::allow() : Response::deny('Somente responsáveis podem gerenciar o grupo.');
    }
}
