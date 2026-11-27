<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Report> */
final class ReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reporter_user_id' => User::factory(),
            'reported_user_id' => User::factory(),
            'reason' => 'assédio',
            'narrative' => fake()->paragraph(),
            'status' => ReportStatus::Received,
            'reviewer_user_id' => null,
            'decision' => null,
            'decided_at' => null,
            'created_at' => now(),
            'deleted_at' => null,
        ];
    }

    public function concluded(): static
    {
        return $this->state(fn (): array => [
            'status' => ReportStatus::Concluded,
            'reviewer_user_id' => User::factory()->admin(),
            'decision' => 'Denúncia revisada e concluída.',
            'decided_at' => now(),
        ]);
    }
}
