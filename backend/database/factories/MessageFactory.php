<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ConversationParticipant;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Message> */
final class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sender_participant_id' => ConversationParticipant::factory(),
            'conversation_id' => fn (array $attributes): int => ConversationParticipant::findOrFail($attributes['sender_participant_id'])->conversation_id,
            'encrypted_body' => random_bytes(64),
            'accepted_at' => now(),
        ];
    }
}
