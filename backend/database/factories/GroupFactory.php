<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Group> */
final class GroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory()->group(),
            'name' => fake()->words(3, true),
        ];
    }
}
