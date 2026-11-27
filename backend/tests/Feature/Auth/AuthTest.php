<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\AccessGrantToken;
use App\Models\AccountBlock;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function credentials(User $user, string $password = 'senha-segura'): array
    {
        return ['email' => $user->email, 'password' => $password, 'device_name' => 'Meu celular'];
    }

    private function user(): User
    {
        return User::factory()->create(['password' => 'senha-segura']);
    }

    private function login(User $user): TestResponse
    {
        $this->app['auth']->forgetGuards();

        return $this->postJson('/api/login', $this->credentials($user));
    }

    private function authenticated(string $method, string $uri, string $token): TestResponse
    {
        // Each HTTP call represents a separate mobile request, with a fresh guard.
        $this->app['auth']->forgetGuards();

        return $this->json($method, $uri, [], ['Authorization' => 'Bearer '.$token]);
    }

    private function registration(): array
    {
        return ['name' => 'Maria Silva', 'email' => 'maria@example.com', 'username' => 'Maria_01', 'password' => 'senha-segura', 'password_confirmation' => 'senha-segura'];
    }

    public function test_register_creates_account_and_logs_in_without_assigning_admin(): void
    {
        $response = $this->postJson('/api/register', $this->registration() + ['is_admin' => true]);
        $response->assertCreated()->assertJsonPath('data.user.username', 'Maria_01')->assertJsonPath('data.user.is_admin', false);
        $user = User::firstOrFail();
        $this->assertTrue(Hash::check('senha-segura', $user->password));
        $this->assertArrayNotHasKey('password', $response->json('data.user'));
        $this->assertArrayNotHasKey('remember_token', $response->json('data.user'));
        $this->assertSame(1, $user->accessGrants()->active()->count());
        $this->authenticated('GET', '/api/me', $response->json('data.token'))->assertOk()->assertJsonPath('data.id', $user->id);
    }

    public static function invalidRegistration(): array
    {
        return [
            'missing name' => ['name', ''],
            'invalid email' => ['email', 'email-invalido'],
            'short username' => ['username', 'ab'],
            'long username' => ['username', str_repeat('a', 31)],
            'invalid username' => ['username', '@Maria'],
            'short password' => ['password', '1234567'],
            'unconfirmed password' => ['password_confirmation', 'outra-senha'],
        ];
    }

    #[DataProvider('invalidRegistration')]
    public function test_register_validates_input(string $field, string $value): void
    {
        $data = array_replace($this->registration(), [$field => $value]);
        $this->postJson('/api/register', $data)->assertUnprocessable()->assertJsonValidationErrors($field === 'password_confirmation' ? 'password' : $field);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('access_grants', 0);
    }

    public function test_username_remains_reserved_case_insensitively_after_deactivation(): void
    {
        User::factory()->create(['username' => 'mArIa_01'])->delete();
        $this->postJson('/api/register', $this->registration())->assertUnprocessable()->assertJsonValidationErrors('username');
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'maria@example.com']);
        $this->postJson('/api/register', $this->registration())->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_login_returns_personal_token_user_and_expiration(): void
    {
        config(['sanctum.expiration' => 60]);
        $user = $this->user();
        $response = $this->login($user)->assertOk()->assertJsonPath('data.user.id', $user->id);
        $token = PersonalAccessToken::findToken($response->json('data.token'));
        $this->assertNotNull($token);
        $this->assertTrue($token->expires_at->between(now()->addMinutes(59), now()->addMinutes(61)));
        $this->assertSame('Meu celular', $token->name);
        $this->assertSame($user->id, $token->accessGrantToken->accessGrant->user_id);
        $this->assertNotSame($response->json('data.token'), $token->token);
    }

    public function test_login_replaces_grant_and_deletes_all_previous_tokens(): void
    {
        $user = $this->user();
        $oldToken = $this->login($user)->json('data.token');
        $grant = $user->accessGrants()->active()->firstOrFail();
        $extra = $user->createToken('Credencial adicional');
        AccessGrantToken::create(['access_grant_id' => $grant->id, 'personal_access_token_id' => $extra->accessToken->id]);
        $newToken = $this->login($user)->assertOk()->json('data.token');
        $this->assertFalse($grant->refresh()->isActive());
        $this->assertSame(1, $user->accessGrants()->active()->count());
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseCount('access_grant_tokens', 1);
        $this->authenticated('GET', '/api/me', $oldToken)->assertUnauthorized();
        $this->authenticated('GET', '/api/me', $extra->plainTextToken)->assertUnauthorized();
        $this->authenticated('GET', '/api/me', $newToken)->assertOk();
    }

    public function test_bad_credentials_do_not_revoke_the_existing_grant(): void
    {
        $user = $this->user();
        $token = $this->login($user)->json('data.token');
        $this->postJson('/api/login', $this->credentials($user, 'senha-errada'))->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->authenticated('GET', '/api/me', $token)->assertOk();
    }

    public function test_unknown_email_returns_validation_error(): void
    {
        $this->postJson('/api/login', ['email' => 'inexistente@example.com', 'password' => 'senha', 'device_name' => 'celular'])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_login_requires_email_password_and_device_name(): void
    {
        $this->postJson('/api/login')->assertUnprocessable()->assertJsonValidationErrors(['email', 'password', 'device_name']);
    }

    public function test_blocked_login_keeps_preexisting_credentials_valid(): void
    {
        $user = $this->user();
        $token = $this->login($user)->json('data.token');
        AccountBlock::factory()->open()->for($user)->create();
        $this->login($user)->assertForbidden()->assertJsonPath('message', 'Esta conta está bloqueada para novos acessos.');
        $this->authenticated('GET', '/api/me', $token)->assertOk();
        $this->assertSame(1, $user->accessGrants()->active()->count());
    }

    public function test_closed_block_allows_login(): void
    {
        $user = $this->user();
        AccountBlock::factory()->closed()->for($user)->create();
        $this->login($user)->assertOk();
    }

    public function test_deactivated_account_cannot_login(): void
    {
        $user = $this->user();
        $user->delete();
        $this->login($user)->assertForbidden()->assertJsonPath('message', 'Esta conta foi desativada.');
        $this->assertDatabaseCount('access_grants', 0);
    }

    public function test_logout_removes_current_token_and_revokes_empty_grant(): void
    {
        $user = $this->user();
        $token = $this->login($user)->json('data.token');
        $this->authenticated('POST', '/api/logout', $token)->assertOk()->assertJsonPath('data.message', 'Sessão encerrada.');
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseCount('access_grant_tokens', 0);
        $this->assertSame(0, $user->accessGrants()->active()->count());
        $this->authenticated('GET', '/api/me', $token)->assertUnauthorized();
    }

    public function test_logout_preserves_grant_while_another_token_remains(): void
    {
        $user = $this->user();
        $first = $this->login($user)->json('data.token');
        $grant = $user->accessGrants()->active()->firstOrFail();
        $second = $user->createToken('adicional');
        AccessGrantToken::create(['access_grant_id' => $grant->id, 'personal_access_token_id' => $second->accessToken->id]);
        $this->authenticated('POST', '/api/logout', $first)->assertOk();
        $this->assertTrue($grant->refresh()->isActive());
        $this->authenticated('GET', '/api/me', $second->plainTextToken)->assertOk();
    }

    public function test_me_and_user_share_resource(): void
    {
        $user = $this->user();
        $token = $this->login($user)->json('data.token');
        foreach (['/api/me', '/api/user'] as $uri) {
            $this->authenticated('GET', $uri, $token)->assertOk()->assertExactJson(['data' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'username' => $user->username, 'is_admin' => false]]);
        }
    }

    public function test_protected_endpoints_require_authentication(): void
    {
        $this->getJson('/api/me')->assertUnauthorized()->assertJsonPath('message', 'Não autenticado. Faça login para continuar.');
        $this->getJson('/api/user')->assertUnauthorized();
        $this->postJson('/api/logout')->assertUnauthorized();
    }

    public function test_revoked_grant_rejects_an_otherwise_valid_token(): void
    {
        $user = $this->user();
        $token = $this->login($user)->json('data.token');
        $user->accessGrants()->active()->get()->each->update(['revoked_at' => now()]);
        $this->authenticated('GET', '/api/me', $token)->assertUnauthorized()->assertJsonPath('message', 'Acesso revogado. Faça login novamente.');
    }

    public function test_unlinked_token_cannot_access_api(): void
    {
        $this->authenticated('GET', '/api/me', $this->user()->createToken('sem grant')->plainTextToken)->assertUnauthorized();
    }

    public function test_expired_token_cannot_access_api(): void
    {
        $user = $this->user();
        $token = $this->login($user)->json('data.token');
        $this->travel(10081)->minutes();
        $this->authenticated('GET', '/api/me', $token)->assertUnauthorized();
    }

    public function test_admin_gate_and_mass_assignment_protection(): void
    {
        $user = $this->user();
        $user->fill(['is_admin' => true])->save();
        $this->assertFalse($user->is_admin);
        $this->assertFalse(Gate::forUser($user)->allows('admin'));
        $this->assertTrue(Gate::forUser(User::factory()->admin()->create())->allows('admin'));
    }

    public function test_login_is_limited_to_six_attempts_per_minute(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/login', ['email' => 'ninguem@example.com', 'password' => 'senha', 'device_name' => 'celular'])->assertUnprocessable();
        }
        $this->postJson('/api/login', ['email' => 'ninguem@example.com', 'password' => 'senha', 'device_name' => 'celular'])->assertStatus(429)->assertJsonPath('message', 'Muitas tentativas. Aguarde um minuto e tente novamente.');
    }
}
