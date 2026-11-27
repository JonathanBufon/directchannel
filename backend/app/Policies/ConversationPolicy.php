<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): Response
    {
        return $conversation->activeParticipantFor($user) !== null
            ? Response::allow()
            : Response::denyAsNotFound('Conversa não encontrada.');
    }

    public function delete(User $user, Conversation $conversation): Response
    {
        return $this->view($user, $conversation);
    }
}
