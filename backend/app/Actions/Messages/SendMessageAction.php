<?php

declare(strict_types=1);

namespace App\Actions\Messages;

use App\Models\Attachment;
use App\Models\Block;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageReceipt;
use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class SendMessageAction
{
    public function execute(Conversation $conversation, User $user, string $encryptedBody, array $uploadIds): Message
    {
        return DB::transaction(function () use ($conversation, $user, $encryptedBody, $uploadIds): Message {
            $conversation = Conversation::whereKey($conversation->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('create', [Message::class, $conversation]);
            $sender = $conversation->activeParticipantFor($user);
            $participants = $conversation->participants()->active()->orderBy('id')->lockForUpdate()->get();
            if (! $conversation->isGroup()) {
                $others = $participants->where('user_id', '!=', $user->id)->pluck('user_id');
                $blocked = Block::query()->where(function ($query) use ($user, $others): void {
                    $query->where('blocker_user_id', $user->id)->whereIn('blocked_user_id', $others);
                })->orWhere(function ($query) use ($user, $others): void {
                    $query->where('blocked_user_id', $user->id)->whereIn('blocker_user_id', $others);
                })->exists();
                abort_if($blocked, 403, 'Não é possível enviar mensagens enquanto houver bloqueio entre os contatos.');
            }
            $uploads = TemporaryUpload::whereIn('id', $uploadIds)->orderBy('id')->lockForUpdate()->get();
            if ($uploads->count() !== count($uploadIds) || $uploads->contains(fn (TemporaryUpload $upload): bool => $upload->user_id !== $user->id || $upload->conversation_id !== $conversation->id
                || $upload->consumed_at !== null || $upload->expires_at->lessThanOrEqualTo(now())
            )) {
                throw ValidationException::withMessages(['temporary_upload_ids' => 'Há uploads inválidos, expirados, já utilizados ou de outra conversa.']);
            }
            // Preserve the same precision used by the history cursor and membership boundary.
            $message = new Message;
            $message->setDateFormat('Y-m-d H:i:s.uP');
            $message->fill([
                'conversation_id' => $conversation->id,
                'sender_participant_id' => $sender->id,
                'encrypted_body' => base64_decode($encryptedBody, true),
                'accepted_at' => now(),
            ]);
            $message->save();
            foreach ($uploads as $upload) {
                Attachment::create(['message_id' => $message->id, 'storage_key' => $upload->storage_key, 'size_bytes' => $upload->size_bytes]);
                $upload->update(['consumed_at' => now()]);
            }
            foreach ($participants as $participant) {
                if ($participant->id !== $sender->id) {
                    MessageReceipt::create(['message_id' => $message->id, 'recipient_participant_id' => $participant->id]);
                }
            }

            return $message->load(['sender.user', 'attachments', 'messageReceipts' => fn ($query) => $query->where('recipient_participant_id', $sender->id)]);
        }, 3);
    }
}
