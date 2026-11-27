<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PublicKeyMaterial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PublicKeyMaterial> */
final class PublicKeyMaterialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'public_payload' => random_bytes(32),
            'published_at' => now(),
        ];
    }
}
