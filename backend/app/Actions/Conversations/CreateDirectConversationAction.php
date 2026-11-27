<?php

declare(strict_types=1);

namespace App\Actions\Conversations;

use App\Enums\ConversationKind;
use App\Models\Block;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class CreateDirectConversationAction
{
    /** @return array{conversation: Conversation, created: bool} */
    public function execute(User $user, int $targetId): array
    {
        return DB::transaction(function () use ($user, $targetId): array {
            $pair = User::whereKey([$user->id, $targetId])->orderBy('id')->lockForUpdate()->get();
            abort_if($pair->count() !== 2, 404, 'Usuário não encontrado.');
            $blocked = Block::where(fn (Builder $query): Builder => $query->where('blocker_user_id', $user->id)->where('blocked_user_id', $targetId))
                ->orWhere(fn (Builder $query): Builder => $query->where('blocker_user_id', $targetId)->where('blocked_user_id', $user->id))->exists();
            abort_if($blocked, 403, 'Não é possível iniciar uma conversa com este contato.');

            // Historical participants identify the pair even when one archived the chat.
            $conversation = Conversation::where('kind', ConversationKind::Direct)
                ->whereHas('participants', fn (Builder $query): Builder => $query->where('user_id', $user->id))
                ->whereHas('participants', fn (Builder $query): Builder => $query->where('user_id', $targetId))
                ->orderBy('id')->lockForUpdate()->first();
            $created = $conversation === null;
            $conversation ??= Conversation::create(['kind' => ConversationKind::Direct, 'created_at' => now()]);
            foreach ($pair as $participantUser) {
                // Reopening an existing chat restores only the caller's participation.
                if (! $created && $participantUser->id !== $user->id) {
                    continue;
                }
                if ($conversation->activeParticipantFor($participantUser) === null) {
                    $conversation->participants()->create(['user_id' => $participantUser->id, 'joined_at' => now()]);
                }
            }

            return ['conversation' => $conversation, 'created' => $created];
        }, 3);
    }
}
