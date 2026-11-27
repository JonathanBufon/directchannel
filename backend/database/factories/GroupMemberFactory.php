<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ConversationParticipant;
use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GroupMember> */
final class GroupMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'participant_id' => fn (array $attributes): int => ConversationParticipant::factory()->create(['conversation_id' => Group::findOrFail($attributes['group_id'])->conversation_id])->id,
            'is_responsible' => true,
        ];
    }
}
