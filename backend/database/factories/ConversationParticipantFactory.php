<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ConversationParticipant> */
final class ConversationParticipantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'user_id' => User::factory(),
            'joined_at' => now(),
            'left_at' => null,
        ];
    }

    public function left(): static
    {
        return $this->state(fn (): array => ['joined_at' => now()->subDay(), 'left_at' => now()]);
    }
}
