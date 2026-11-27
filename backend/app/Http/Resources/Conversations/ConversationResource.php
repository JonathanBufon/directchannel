<?php

declare(strict_types=1);

namespace App\Http\Resources\Conversations;

use App\Http\Resources\Users\PublicUserResource;
use App\Models\ConversationParticipant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $message = $this->latestMessage;

        return [
            'id' => $this->id,
            'kind' => $this->kind->value,
            'created_at' => $this->created_at,
            'group' => $this->group === null ? null : ['id' => $this->group->id, 'name' => $this->group->name],
            'participants' => $this->participants->where('user_id', '!=', $request->user()->id)
                ->map(fn (ConversationParticipant $participant): array => [
                    'id' => $participant->id, 'user_id' => $participant->user_id,
                    'user' => $participant->user === null ? null : new PublicUserResource($participant->user),
                ])->values(),
            'latest_message' => $message === null ? null : [
                'id' => $message->id, 'accepted_at' => $message->accepted_at,
                'sender' => [
                    'id' => $message->sender->id, 'user_id' => $message->sender->user_id,
                    'user' => $message->sender->user === null ? null : new PublicUserResource($message->sender->user),
                ],
            ],
        ];
    }
}
