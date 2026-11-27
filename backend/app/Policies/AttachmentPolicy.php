<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Attachment;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class AttachmentPolicy
{
    public function create(User $user, Conversation $conversation): Response
    {
        return $conversation->activeParticipantFor($user) !== null
            ? Response::allow()
            : Response::denyAsNotFound('Conversa não encontrada.');
    }

    public function view(User $user, Attachment $attachment): Response
    {
        $message = $attachment->message;
        $participant = $message->conversation->activeParticipantFor($user);

        return $participant?->canSee($message) === true
            ? Response::allow()
            : Response::denyAsNotFound('Anexo não encontrado.');
    }

    public function update(User $user, Attachment $attachment): Response
    {
        $visibility = $this->view($user, $attachment);
        if ($visibility->denied()) {
            return $visibility;
        }

        return $attachment->message->sender->user_id === $user->id
            ? Response::allow()
            : Response::deny('Somente o autor da mensagem pode alterar o anexo.');
    }

    public function delete(User $user, Attachment $attachment): Response
    {
        return $this->update($user, $attachment);
    }
}
