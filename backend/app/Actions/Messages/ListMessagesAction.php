<?php

declare(strict_types=1);

namespace App\Actions\Messages;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

final class ListMessagesAction
{
    public function execute(Conversation $conversation, User $user, ?int $beforeId, int $limit): Collection
    {
        Gate::authorize('viewAny', [Message::class, $conversation]);
        $participant = $conversation->activeParticipantFor($user);
        $query = $conversation->messages()
            ->where('accepted_at', '>=', $participant->joined_at->format('Y-m-d H:i:s.uP'));
        if ($beforeId !== null) {
            $cursor = (clone $query)->whereKey($beforeId)->firstOrFail();
            $acceptedAt = $cursor->getRawOriginal('accepted_at');
            $query->where(function ($query) use ($acceptedAt, $cursor): void {
                $query->where('accepted_at', '<', $acceptedAt)
                    ->orWhere(function ($query) use ($acceptedAt, $cursor): void {
                        $query->where('accepted_at', $acceptedAt)->where('id', '<', $cursor->id);
                    });
            });
        }

        return $query->with([
            'sender.user' => fn ($query) => $query->withTrashed(),
            'attachments',
            'messageReceipts' => fn ($query) => $query->where('recipient_participant_id', $participant->id),
        ])->orderByDesc('accepted_at')->orderByDesc('id')->limit($limit)->get();
    }
}
