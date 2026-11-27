<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Attachment> */
final class AttachmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'message_id' => Message::factory(),
            'storage_key' => 'attachments/'.fake()->uuid(),
            'size_bytes' => fake()->numberBetween(1, 5_000_000),
        ];
    }
}
