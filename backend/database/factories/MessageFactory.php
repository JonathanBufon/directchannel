<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_participant_id' => ConversationParticipant::factory(),
            'encrypted_body' => fake()->sha256(),
            'accepted_at' => fake()->dateTime(),
        ];
    }
}
