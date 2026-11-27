<?php

namespace Database\Factories;

use App\Models\AccessGrant;
use App\Models\PersonalAccessToken;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccessGrantTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'personal_access_token_id' => PersonalAccessToken::factory(),
            'access_grant_id' => AccessGrant::factory(),
        ];
    }
}
