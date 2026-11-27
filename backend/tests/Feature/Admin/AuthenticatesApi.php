<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AccessGrantToken;
use App\Models\User;

trait AuthenticatesApi
{
    private function signIn(?User $user = null): User
    {
        $user ??= User::factory()->create();
        $grant = $user->accessGrants()->active()->first() ?? $user->accessGrants()->create(['started_at' => now()]);
        $token = $user->createToken('testes', ['*'], now()->addHour());
        AccessGrantToken::create(['access_grant_id' => $grant->id, 'personal_access_token_id' => $token->accessToken->id]);
        $this->app['auth']->forgetGuards();
        $this->withToken($token->plainTextToken);

        return $user;
    }
}
