<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageReceipt;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MessageReceipt> */
final class MessageReceiptFactory extends Factory
{
    public function definition(): array
    {
        return [
            'message_id' => Message::factory(),
            'recipient_participant_id' => fn (array $attributes): int => ConversationParticipant::factory()->create(['conversation_id' => Message::findOrFail($attributes['message_id'])->conversation_id])->id,
            'delivered_at' => null,
            'read_at' => null,
        ];
    }
}
