<?php

declare(strict_types=1);

namespace App\Http\Resources\Messages;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $receipt = $this->messageReceipts->first();
        $sender = $this->sender->user;

        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'encrypted_body' => base64_encode($this->encrypted_body),
            'accepted_at' => $this->accepted_at->toISOString(),
            'sender' => ['id' => $sender->id, 'name' => $sender->name, 'username' => $sender->username],
            'attachments' => $this->attachments->map(fn ($attachment): array => ['id' => $attachment->id, 'size_bytes' => $attachment->size_bytes]),
            'receipt' => $receipt === null ? null : [
                'delivered_at' => $receipt->delivered_at?->toISOString(),
                'read_at' => $receipt->read_at?->toISOString(),
            ],
        ];
    }
}
