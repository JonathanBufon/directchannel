<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountBlockFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'blocked_by_user_id' => User::factory(),
            'reason' => fake()->text(),
            'blocked_at' => fake()->dateTime(),
            'unblocked_by_user_id' => User::factory(),
            'unblocked_at' => fake()->dateTime(),
        ];
    }
}
