<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ConversationKind;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Conversation> */
final class ConversationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kind' => ConversationKind::Direct,
            'created_at' => now(),
        ];
    }

    public function direct(): static
    {
        return $this->state(fn (): array => ['kind' => ConversationKind::Direct]);
    }

    public function group(): static
    {
        return $this->state(fn (): array => ['kind' => ConversationKind::Group]);
    }
}
