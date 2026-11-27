<?php

namespace Database\Factories;

use App\Models\ConversationParticipant;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'participant_id' => ConversationParticipant::factory(),
            'group_id' => Group::factory(),
            'is_responsible' => fake()->boolean(),
        ];
    }
}
