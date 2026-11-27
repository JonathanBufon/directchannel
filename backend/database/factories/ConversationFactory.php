<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'kind' => fake()->regexify('[A-Za-z0-9]{16}'),
            'created_at' => fake()->dateTime(),
        ];
    }
}
