<?php

declare(strict_types=1);

namespace App\Actions\Conversations;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListConversationsAction
{
    public function execute(User $user): LengthAwarePaginator
    {
        $latestVisible = Message::query()->visibleTo($user)
            ->whereColumn('messages.conversation_id', 'conversations.id')->selectRaw('MAX(accepted_at)');

        return Conversation::query()
            ->whereHas('participants', fn (Builder $query): Builder => $query->active()->where('user_id', $user->id))
            ->withVisibleDetails($user)
            ->orderByRaw('COALESCE(('.$latestVisible->toSql().'), conversations.created_at) DESC', $latestVisible->getBindings())
            ->orderByDesc('id')->paginate(20);
    }
}
