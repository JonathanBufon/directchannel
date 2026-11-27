<?php

declare(strict_types=1);

namespace App\Actions\Conversations;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class ArchiveConversationAction
{
    public function execute(User $user, Conversation $conversation): void
    {
        DB::transaction(function () use ($user, $conversation): void {
            $locked = Conversation::whereKey($conversation->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($user)->authorize('delete', $locked);
            if ($locked->isGroup()) {
                throw ValidationException::withMessages(['conversation' => 'Para sair de um grupo, use o fluxo de sair do grupo.']);
            }
            $locked->activeParticipantFor($user)->update(['left_at' => now()]);
        }, 3);
    }
}
