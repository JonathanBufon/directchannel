<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Models\AccessGrantToken;
use App\Models\PersonalAccessToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Users\ApiTestCase;

final class ProfileTest extends ApiTestCase
{
    public function test_update_profile_allows_own_username_case_and_rejects_privileged_fields(): void
    {
        $user = $this->user(['username' => 'Maria']);
        $this->signIn($user);
        $this->api('PATCH', '/api/me', ['name' => 'Maria Silva', 'username' => 'mARIA', 'is_admin' => true, 'password' => 'injetada', 'read_receipts_enabled' => false])
            ->assertOk()->assertJsonPath('data.name', 'Maria Silva')->assertJsonPath('data.username', 'mARIA')->assertJsonPath('data.is_admin', false);
        $this->assertTrue(Hash::check('senha-segura', $user->refresh()->password));
        $this->assertTrue($user->read_receipts_enabled);
    }

    public function test_change_email_requires_current_password_and_clears_verification(): void
    {
        $user = $this->user();
        $this->signIn($user);
        $this->api('PATCH', '/api/me', ['email' => 'novo@example.com'])->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->api('PATCH', '/api/me', ['email' => 'novo@example.com', 'current_password' => 'incorreta'])->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->api('PATCH', '/api/me', ['email' => 'novo@example.com', 'current_password' => 'senha-segura'])->assertOk()->assertJsonPath('data.email', 'novo@example.com');
        $this->assertNull($user->refresh()->email_verified_at);
    }

    public function test_username_and_email_remain_reserved_by_deleted_accounts(): void
    {
        $other = $this->user(['username' => 'Reservado', 'email' => 'reservado@example.com']);
        $other->delete();
        $this->signIn($this->user());
        $this->api('PATCH', '/api/me', ['username' => 'RESERVADO'])->assertUnprocessable()->assertJsonValidationErrors('username');
        $this->api('PATCH', '/api/me', ['email' => 'reservado@example.com', 'current_password' => 'senha-segura'])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    #[DataProvider('invalidProfiles')]
    public function test_profile_validation(string $field, mixed $value): void
    {
        $this->signIn($this->user());
        $this->api('PATCH', '/api/me', [$field => $value])->assertUnprocessable()->assertJsonValidationErrors($field);
    }

    public static function invalidProfiles(): array
    {
        return [['name', ''], ['name', str_repeat('a', 256)], ['username', 'ab'], ['username', '@abc'], ['username', str_repeat('x', 31)], ['email', 'inválido']];
    }

    public function test_change_password_keeps_current_token_and_revokes_other_tokens_with_their_bridges(): void
    {
        $user = $this->user();
        $current = $this->signIn($user);
        $grant = $user->accessGrants()->active()->firstOrFail();
        $extra = $user->createToken('Outro token');
        AccessGrantToken::create(['access_grant_id' => $grant->id, 'personal_access_token_id' => $extra->accessToken->id]);
        DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => 'hash-antigo', 'created_at' => now()]);
        $this->api('PATCH', '/api/me/password', ['current_password' => 'senha-segura', 'password' => 'senha-nova-segura', 'password_confirmation' => 'senha-nova-segura'])->assertOk();
        $this->assertTrue(Hash::check('senha-nova-segura', $user->refresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseCount('access_grant_tokens', 1);
        $this->assertDatabaseCount('password_reset_tokens', 0);
        $this->assertTrue($grant->refresh()->isActive());
        $this->assertNotNull(PersonalAccessToken::findToken($current));
        $this->api('GET', '/api/me', [], $extra->plainTextToken)->assertUnauthorized();
        $this->api('GET', '/api/me', [], $current)->assertOk();
    }

    #[DataProvider('invalidPasswords')]
    public function test_password_change_validation(array $data, string $field): void
    {
        $user = $this->user();
        $this->signIn($user);
        $this->api('PATCH', '/api/me/password', $data)->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertTrue(Hash::check('senha-segura', $user->refresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public static function invalidPasswords(): array
    {
        $valid = ['current_password' => 'senha-segura', 'password' => 'nova-senha-segura', 'password_confirmation' => 'nova-senha-segura'];

        return [
            [[], 'current_password'], [array_replace($valid, ['current_password' => 'errada']), 'current_password'],
            [array_replace($valid, ['password' => 'curta', 'password_confirmation' => 'curta']), 'password'],
            [array_replace($valid, ['password_confirmation' => 'errada']), 'password'],
        ];
    }

    public function test_deactivation_soft_deletes_user_and_revokes_every_grant_and_token(): void
    {
        $user = $this->user();
        $token = $this->signIn($user);
        $grant = $user->accessGrants()->active()->firstOrFail();
        $extra = $user->createToken('Outro');
        AccessGrantToken::create(['access_grant_id' => $grant->id, 'personal_access_token_id' => $extra->accessToken->id]);
        $this->api('DELETE', '/api/me', ['password' => 'senha-segura'])->assertOk();
        $this->assertSoftDeleted($user);
        $this->assertFalse($grant->refresh()->isActive());
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseCount('access_grant_tokens', 0);
        $this->api('GET', '/api/me', [], $token)->assertUnauthorized();
    }

    public function test_deactivation_requires_password_without_mutating_the_account(): void
    {
        $user = $this->user();
        $this->signIn($user);
        $this->api('DELETE', '/api/me')->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->api('DELETE', '/api/me', ['password' => 'errada'])->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->assertFalse($user->refresh()->trashed());
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_privacy_defaults_and_partial_updates_only_affect_own_account(): void
    {
        $user = $this->user();
        $other = $this->user();
        $this->signIn($user);
        $this->api('GET', '/api/me/privacy')->assertExactJson(['data' => ['read_receipts_enabled' => true, 'notification_preview_enabled' => true]]);
        $this->api('PATCH', '/api/me/privacy', ['read_receipts_enabled' => false, 'user_id' => $other->id])->assertExactJson(['data' => ['read_receipts_enabled' => false, 'notification_preview_enabled' => true]]);
        $this->api('PATCH', '/api/me/privacy', ['notification_preview_enabled' => false])->assertJsonPath('data.read_receipts_enabled', false)->assertJsonPath('data.notification_preview_enabled', false);
        $this->api('GET', '/api/me/privacy')->assertJsonPath('data.read_receipts_enabled', false);
        $this->assertTrue($other->refresh()->read_receipts_enabled);
    }

    public function test_privacy_rejects_empty_patch_and_non_boolean_fields(): void
    {
        $this->signIn($this->user());
        $this->api('PATCH', '/api/me/privacy')->assertUnprocessable();
        $this->api('PATCH', '/api/me/privacy', ['read_receipts_enabled' => 'false', 'notification_preview_enabled' => 'talvez'])->assertUnprocessable()->assertJsonValidationErrors(['read_receipts_enabled', 'notification_preview_enabled']);
    }
}
