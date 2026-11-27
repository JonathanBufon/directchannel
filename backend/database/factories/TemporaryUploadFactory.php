<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TemporaryUpload> */
final class TemporaryUploadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'conversation_id' => Conversation::factory(),
            'storage_key' => 'temporary/'.fake()->uuid(),
            'original_name' => 'arquivo.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => fake()->numberBetween(1, 5_000_000),
            'expires_at' => now()->addHour(),
            'consumed_at' => null,
            'created_at' => now(),
        ];
    }
}
