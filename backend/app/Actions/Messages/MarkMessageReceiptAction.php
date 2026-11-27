<?php

declare(strict_types=1);

namespace App\Actions\Messages;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageReceipt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class MarkMessageReceiptAction
{
    public function execute(Message $message, User $user, bool $read): void
    {
        DB::transaction(function () use ($message, $user, $read): void {
            $conversation = Conversation::whereKey($message->conversation_id)->lockForUpdate()->firstOrFail();
            $message->setRelation('conversation', $conversation);
            Gate::authorize('view', $message);
            $participant = $conversation->activeParticipantFor($user);
            $query = MessageReceipt::where('message_id', $message->id)->where('recipient_participant_id', $participant->id);
            $receipt = (clone $query)->lockForUpdate()->firstOrFail();
            $now = now();
            $values = [];
            if ($receipt->delivered_at === null) {
                $values['delivered_at'] = $now;
            }
            if ($read && $user->read_receipts_enabled && $receipt->read_at === null) {
                $values['read_at'] = $now;
            }
            if ($values !== []) {
                $query->update($values);
            }
        }, 3);
    }
}
