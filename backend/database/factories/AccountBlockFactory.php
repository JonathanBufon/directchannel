<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AccountBlock;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AccountBlock> */
final class AccountBlockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'blocked_by_user_id' => User::factory()->admin(),
            'reason' => 'Violação dos termos de uso.',
            'blocked_at' => now(),
            'unblocked_by_user_id' => null,
            'unblocked_at' => null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn (): array => ['unblocked_by_user_id' => null, 'unblocked_at' => null]);
    }

    public function closed(): static
    {
        return $this->state(fn (): array => ['blocked_at' => now()->subDay(), 'unblocked_by_user_id' => User::factory()->admin(), 'unblocked_at' => now()]);
    }
}
