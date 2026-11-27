<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Actions\Auth\LoginAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected ?string $token = null;

    protected function user(array $attributes = []): User
    {
        return User::factory()->create(array_replace(['password' => 'senha-segura'], $attributes));
    }

    protected function signIn(User $user): string
    {
        $this->token = app(LoginAction::class)->execute($user->email, 'senha-segura', 'Celular de teste')['token'];

        return $this->token;
    }

    protected function api(string $method, string $uri, array $data = [], ?string $token = null): TestResponse
    {
        $this->app['auth']->forgetGuards();
        $bearer = $token ?? $this->token;

        return $this->json($method, $uri, $data, $bearer === null ? [] : ['Authorization' => 'Bearer '.$bearer]);
    }
}
