<?php

declare(strict_types=1);

namespace App\Actions\Conversations;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ShowConversationAction
{
    public function execute(Conversation $conversation): Conversation
    {
        return $conversation->load(['participants' => fn (HasMany $query): HasMany => $query->active()->orderBy('id'), 'participants.user', 'latestMessage:id,conversation_id,sender_participant_id,accepted_at', 'latestMessage.sender.user', 'group']);
    }
}
