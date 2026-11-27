<?php

declare(strict_types=1);

namespace App\Actions\Conversations;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ListConversationsAction
{
    public function execute(User $user): LengthAwarePaginator
    {
        return Conversation::query()
            ->whereHas('participants', fn (Builder $query): Builder => $query->active()->where('user_id', $user->id))
            ->with(['participants' => fn (HasMany $query): HasMany => $query->active()->orderBy('id'), 'participants.user', 'latestMessage:id,conversation_id,sender_participant_id,accepted_at', 'latestMessage.sender.user', 'group'])
            ->orderByRaw('COALESCE((SELECT MAX(accepted_at) FROM messages WHERE messages.conversation_id = conversations.id), conversations.created_at) DESC')
            ->orderByDesc('id')->paginate(20);
    }
}
