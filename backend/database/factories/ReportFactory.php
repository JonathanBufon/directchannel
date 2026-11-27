<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'reporter_user_id' => User::factory(),
            'reported_user_id' => User::factory(),
            'reason' => fake()->regexify('[A-Za-z0-9]{50}'),
            'narrative' => fake()->text(),
            'status' => fake()->regexify('[A-Za-z0-9]{16}'),
            'reviewer_user_id' => User::factory(),
            'decision' => fake()->text(),
            'decided_at' => fake()->dateTime(),
            'created_at' => fake()->dateTime(),
            'deleted_at' => fake()->dateTime(),
        ];
    }
}
