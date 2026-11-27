<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Block;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Block> */
final class BlockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'blocker_user_id' => User::factory(),
            'blocked_user_id' => User::factory(),
        ];
    }
}
