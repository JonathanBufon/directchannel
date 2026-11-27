<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'message_id' => Message::factory(),
            'storage_key' => fake()->regexify('[A-Za-z0-9]{255}'),
            'size_bytes' => fake()->randomNumber(),
        ];
    }
}
