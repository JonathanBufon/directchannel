<?php

namespace Database\Factories;

use App\Models\ConversationParticipant;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageReceiptFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'message_id' => Message::factory(),
            'recipient_participant_id' => ConversationParticipant::factory(),
            'delivered_at' => fake()->dateTime(),
            'read_at' => fake()->dateTime(),
        ];
    }
}
