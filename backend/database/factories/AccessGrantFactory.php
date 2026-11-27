<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AccessGrant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AccessGrant> */
final class AccessGrantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'started_at' => now(),
            'revoked_at' => null,
        ];
    }

    public function revoked(): static
    {
        return $this->state(fn (): array => ['revoked_at' => now()]);
    }
}
