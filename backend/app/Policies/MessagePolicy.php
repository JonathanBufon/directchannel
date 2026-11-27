<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class MessagePolicy
{
    public function viewAny(User $user, Conversation $conversation): Response
    {
        return $conversation->activeParticipantFor($user) !== null
            ? Response::allow() : Response::denyAsNotFound('Conversa não encontrada.');
    }

    public function create(User $user, Conversation $conversation): Response
    {
        return $this->viewAny($user, $conversation);
    }

    public function view(User $user, Message $message): Response
    {
        $participant = $message->conversation->activeParticipantFor($user);

        return $participant?->canSee($message) === true
            ? Response::allow() : Response::denyAsNotFound('Mensagem não encontrada.');
    }
}
