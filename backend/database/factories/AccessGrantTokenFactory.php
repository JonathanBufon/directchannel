<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AccessGrant;
use App\Models\AccessGrantToken;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AccessGrantToken> */
final class AccessGrantTokenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'access_grant_id' => AccessGrant::factory(),
            'personal_access_token_id' => fn (array $attributes): int => AccessGrant::findOrFail($attributes['access_grant_id'])->user->createToken('factory')->accessToken->getKey(),
        ];
    }
}
