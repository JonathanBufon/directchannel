<?php

declare(strict_types=1);

namespace App\Http\Resources\Groups;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class GroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewer = $this->activeMembers->first(fn ($member): bool => $member->participant->user_id === $request->user()->id);

        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'name' => $this->name,
            'member_count' => $this->activeMembers->count(),
            'is_responsible' => $viewer?->is_responsible ?? false,
            'members' => $this->activeMembers->map(fn ($member): array => [
                'id' => $member->participant->user->id,
                'name' => $member->participant->user->name,
                'username' => $member->participant->user->username,
                'participant_id' => $member->participant_id,
                'is_responsible' => $member->is_responsible,
                'joined_at' => $member->participant->joined_at->toISOString(),
            ]),
        ];
    }
}
