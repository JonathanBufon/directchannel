<?php

declare(strict_types=1);

namespace App\Actions\Conversations;

use App\Models\Conversation;
use App\Models\User;

final class ShowConversationAction
{
    public function execute(Conversation $conversation, User $user): Conversation
    {
        return Conversation::query()->withVisibleDetails($user)->findOrFail($conversation->id);
    }
}
