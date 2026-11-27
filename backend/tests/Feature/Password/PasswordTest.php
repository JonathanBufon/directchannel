<?php

declare(strict_types=1);

namespace Tests\Feature\Password;

use App\Models\AccessGrantToken;
use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Feature\Users\ApiTestCase;

final class PasswordTest extends ApiTestCase
{
    public function test_forgot_always_has_same_generic_success_and_stores_only_hashed_code(): void
    {
        Notification::fake();
        $user = $this->user();
        $deleted = $this->user();
        $deleted->delete();
        $response = $this->api('POST', '/api/password/forgot', ['email' => $user->email])->assertOk();
        $this->api('POST', '/api/password/forgot', ['email' => 'inexistente@example.com'])->assertOk()->assertExactJson($response->json());
        $this->api('POST', '/api/password/forgot', ['email' => $deleted->email])->assertOk()->assertExactJson($response->json());
        $this->api('POST', '/api/password/forgot', ['email' => $user->email])->assertOk()->assertExactJson($response->json());
        Notification::assertSentToTimes($user, PasswordResetCodeNotification::class, 1);
        Notification::assertNotSentTo($deleted, PasswordResetCodeNotification::class);
        Notification::assertCount(1);
        $this->assertDatabaseCount('password_reset_tokens', 1);
        Notification::assertSentTo($user, PasswordResetCodeNotification::class, function (PasswordResetCodeNotification $notification, array $channels) use ($user): bool {
            $stored = DB::table('password_reset_tokens')->where('email', $user->email)->first();
            $this->assertMatchesRegularExpression('/^\d{6}$/D', $notification->code);
            $this->assertNotSame($notification->code, $stored->token);
            $this->assertTrue(Hash::check($notification->code, $stored->token));
            $this->assertSame(0, $stored->attempts);
            $this->assertSame(['mail'], $channels);
            $mail = $notification->toMail($user);
            $this->assertSame('Redefinir senha do DirectChannel', $mail->subject);
            $this->assertContains('Seu código é '.$notification->code.', expira em 15 minutos.', $mail->introLines);
            $this->assertNull($mail->actionUrl);
            $this->assertStringContainsString($notification->code, (string) $mail->render());

            return true;
        });
    }

    public function test_forgot_does_not_replace_code_before_sixty_seconds(): void
    {
        Notification::fake();
        $user = $this->user();
        $code = $this->requestCode($user);
        $this->travel(59)->seconds();
        $this->api('POST', '/api/password/forgot', ['email' => $user->email])->assertOk();
        Notification::assertSentToTimes($user, PasswordResetCodeNotification::class, 1);
        $this->api('POST', '/api/password/reset', $this->resetData($user, $code))->assertOk();
    }

    public function test_forgot_validates_email_and_throttles_requests(): void
    {
        $this->api('POST', '/api/password/forgot', ['email' => 'inválido'])->assertUnprocessable()->assertJsonValidationErrors('email');
        for ($i = 0; $i < 5; $i++) {
            $this->api('POST', '/api/password/forgot', ['email' => 'inexistente@example.com'])->assertOk();
        }
        $this->api('POST', '/api/password/forgot', ['email' => 'inexistente@example.com'])->assertStatus(429);
    }

    public function test_forgot_preserves_notification_cooldown_after_code_consumption(): void
    {
        Notification::fake();
        $this->freezeTime();
        $user = $this->user();
        $code = $this->requestCode($user);
        $this->api('POST', '/api/password/reset', $this->resetData($user, $code))->assertOk();
        $this->api('POST', '/api/password/forgot', ['email' => $user->email])->assertOk();
        Notification::assertSentToTimes($user, PasswordResetCodeNotification::class, 1);
        $this->assertDatabaseCount('password_reset_tokens', 0);
        $this->travel(60)->seconds();
        $this->requestCode($user);
        Notification::assertSentToTimes($user, PasswordResetCodeNotification::class, 2);
    }

    public function test_forgot_preserves_notification_cooldown_after_five_failed_attempts(): void
    {
        Notification::fake();
        $this->freezeTime();
        $user = $this->user();
        $code = $this->requestCode($user);
        $wrong = $code === '000000' ? '000001' : '000000';
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->api('POST', '/api/password/reset', $this->resetData($user, $wrong))->assertUnprocessable();
        }
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.2']);
        $this->api('POST', '/api/password/forgot', ['email' => $user->email])->assertOk();
        Notification::assertSentToTimes($user, PasswordResetCodeNotification::class, 1);
        $this->assertDatabaseCount('password_reset_tokens', 0);
        $this->travel(60)->seconds();
        $this->requestCode($user);
        Notification::assertSentToTimes($user, PasswordResetCodeNotification::class, 2);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email, 'attempts' => 0]);
    }

    public function test_reset_consumes_code_changes_password_and_revokes_all_access(): void
    {
        Notification::fake();
        Event::fake([PasswordReset::class]);
        $user = $this->user();
        $rememberToken = $user->remember_token;
        $oldBearer = $this->signIn($user);
        $grant = $user->accessGrants()->active()->firstOrFail();
        $extra = $user->createToken('Extra');
        AccessGrantToken::create(['access_grant_id' => $grant->id, 'personal_access_token_id' => $extra->accessToken->id]);
        $this->token = null;
        $code = $this->requestCode($user);
        $this->api('POST', '/api/password/reset', $this->resetData($user, $code))->assertOk();
        $this->assertTrue(Hash::check('nova-senha-segura', $user->refresh()->password));
        $this->assertNotSame($rememberToken, $user->remember_token);
        $this->assertSame(60, strlen($user->remember_token));
        $this->assertDatabaseCount('password_reset_tokens', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseCount('access_grant_tokens', 0);
        $this->assertFalse($grant->refresh()->isActive());
        Event::assertDispatched(PasswordReset::class, fn (PasswordReset $event): bool => $event->user->is($user));
        $this->api('GET', '/api/me', [], $oldBearer)->assertUnauthorized();
        $this->api('GET', '/api/me', [], $extra->plainTextToken)->assertUnauthorized();
        $this->api('POST', '/api/login', ['email' => $user->email, 'password' => 'nova-senha-segura', 'device_name' => 'Celular'])->assertOk();
    }

    public function test_wrong_code_increments_attempts_without_changing_password_or_revoking_access(): void
    {
        Notification::fake();
        Event::fake([PasswordReset::class]);
        $user = $this->user();
        $this->signIn($user);
        $code = $this->requestCode($user);
        $wrong = $code === '000000' ? '000001' : '000000';
        $this->api('POST', '/api/password/reset', $this->resetData($user, $wrong))
            ->assertUnprocessable()->assertJsonValidationErrors('code')
            ->assertJsonPath('errors.code.0', 'Código inválido ou expirado.');
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email, 'attempts' => 1]);
        $this->assertTrue(Hash::check('senha-segura', $user->refresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 1);
        Event::assertNotDispatched(PasswordReset::class);
        $this->api('POST', '/api/password/reset', $this->resetData($user, $code))->assertOk();
    }

    public function test_code_expires_after_fifteen_minutes(): void
    {
        Notification::fake();
        $this->freezeTime();
        $user = $this->user();
        $code = $this->requestCode($user);
        $this->travel(15)->minutes();
        $this->api('POST', '/api/password/reset', $this->resetData($user, $code))
            ->assertUnprocessable()->assertJsonPath('errors.code.0', 'Código inválido ou expirado.');
        $this->assertTrue(Hash::check('senha-segura', $user->refresh()->password));
    }

    public function test_code_remains_valid_just_before_expiry(): void
    {
        Notification::fake();
        $this->freezeTime();
        $user = $this->user();
        $code = $this->requestCode($user);
        $this->travel(899)->seconds();
        $this->api('POST', '/api/password/reset', $this->resetData($user, $code))->assertOk();
    }

    public function test_five_wrong_attempts_invalidate_even_the_correct_code(): void
    {
        Notification::fake();
        $user = $this->user();
        $code = $this->requestCode($user);
        $wrong = $code === '000000' ? '000001' : '000000';
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->api('POST', '/api/password/reset', $this->resetData($user, $wrong))
                ->assertUnprocessable()->assertJsonPath('errors.code.0', 'Código inválido ou expirado.');
            if ($attempt < 5) {
                $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email, 'attempts' => $attempt]);
            }
        }
        $this->assertDatabaseCount('password_reset_tokens', 0);
        // Forgot and reset share the route throttle; wait to test code invalidation.
        $this->travel(61)->seconds();
        $this->api('POST', '/api/password/reset', $this->resetData($user, $code))
            ->assertUnprocessable()->assertJsonPath('errors.code.0', 'Código inválido ou expirado.');
        $this->assertTrue(Hash::check('senha-segura', $user->refresh()->password));
    }

    public function test_code_cannot_be_reused(): void
    {
        Notification::fake();
        $user = $this->user();
        $code = $this->requestCode($user);
        $this->api('POST', '/api/password/reset', $this->resetData($user, $code))->assertOk();
        $this->api('POST', '/api/password/reset', $this->resetData($user, $code))
            ->assertUnprocessable()->assertJsonPath('errors.code.0', 'Código inválido ou expirado.');
    }

    public function test_new_forgot_invalidates_previous_code_and_resets_attempts(): void
    {
        Notification::fake();
        $this->freezeTime();
        $user = $this->user();
        // A stored hash outside the six-digit domain guarantees a distinct replacement.
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email, 'token' => Hash::make('previous-code'), 'created_at' => now(), 'attempts' => 4,
        ]);
        $this->travel(60)->seconds();
        $code = $this->requestCode($user);
        $this->assertDatabaseCount('password_reset_tokens', 1);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email, 'attempts' => 0]);
        $this->assertFalse(Hash::check('previous-code', DB::table('password_reset_tokens')->where('email', $user->email)->value('token')));
        $this->api('POST', '/api/password/reset', $this->resetData($user, $code))->assertOk();
    }

    public function test_new_forgot_replaces_the_code_received_in_the_previous_notification(): void
    {
        Notification::fake();
        $user = $this->user();
        $previous = $this->requestCode($user);
        $this->travel(60)->seconds();
        $this->api('POST', '/api/password/forgot', ['email' => $user->email])->assertOk();
        Notification::assertSentToTimes($user, PasswordResetCodeNotification::class, 2);
        $current = Notification::sent($user, PasswordResetCodeNotification::class)->last()->code;
        $this->api('POST', '/api/password/reset', $this->resetData($user, $previous))
            ->assertUnprocessable()->assertJsonPath('errors.code.0', 'Código inválido ou expirado.');
        $this->api('POST', '/api/password/reset', $this->resetData($user, $current))->assertOk();
    }

    #[DataProvider('invalidResetInputs')]
    public function test_reset_validates_input(array $changes, array $fields): void
    {
        Notification::fake();
        $user = $this->user();
        $code = $this->requestCode($user);
        $data = array_replace($this->resetData($user, $code), $changes);
        $this->api('POST', '/api/password/reset', $data)->assertUnprocessable()->assertJsonValidationErrors($fields);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email, 'attempts' => 0]);
        $this->assertTrue(Hash::check('senha-segura', $user->refresh()->password));
    }

    public static function invalidResetInputs(): array
    {
        return [
            'missing fields' => [['email' => null, 'code' => null, 'password' => null, 'password_confirmation' => null], ['email', 'code', 'password', 'password_confirmation']],
            'short code' => [['code' => '12345'], ['code']],
            'alphabetic code' => [['code' => 'abcdef'], ['code']],
            'long code' => [['code' => '1234567'], ['code']],
            'numeric rather than string code' => [['code' => 123456], ['code']],
            'old token field' => [['code' => null, 'token' => '123456'], ['code']],
            'mismatched confirmation' => [['password_confirmation' => 'outra-senha'], ['password']],
            'short password' => [['password' => 'curta', 'password_confirmation' => 'curta'], ['password']],
            'missing confirmation' => [['password_confirmation' => null], ['password_confirmation']],
            'invalid email' => [['email' => 'inválido'], ['email']],
        ];
    }

    public function test_reset_does_not_accept_a_code_for_a_deactivated_account(): void
    {
        Notification::fake();
        $user = $this->user();
        $code = $this->requestCode($user);
        $user->delete();
        $this->api('POST', '/api/password/reset', $this->resetData($user, $code))
            ->assertUnprocessable()->assertJsonPath('errors.code.0', 'Código inválido ou expirado.');
        $this->assertTrue(Hash::check('senha-segura', $user->refresh()->password));
    }

    public function test_reset_cannot_use_another_accounts_code(): void
    {
        Notification::fake();
        $user = $this->user();
        $other = $this->user();
        $code = $this->requestCode($user);
        $this->api('POST', '/api/password/reset', $this->resetData($other, $code))
            ->assertUnprocessable()->assertJsonPath('errors.code.0', 'Código inválido ou expirado.');
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email, 'attempts' => 0]);
    }

    public function test_reset_unknown_email_uses_the_same_invalid_code_error_and_is_throttled(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->api('POST', '/api/password/reset', [
                'email' => 'inexistente@example.com', 'code' => '123456',
                'password' => 'nova-senha-segura', 'password_confirmation' => 'nova-senha-segura',
            ])->assertUnprocessable()->assertJsonPath('errors.code.0', 'Código inválido ou expirado.');
        }
        $this->api('POST', '/api/password/reset')->assertStatus(429);
    }

    public function test_reset_rolls_back_code_consumption_password_and_revocation_on_failure(): void
    {
        Notification::fake();
        $user = $this->user();
        $this->signIn($user);
        $rememberToken = $user->remember_token;
        $grant = $user->accessGrants()->active()->firstOrFail();
        $code = $this->requestCode($user);
        Event::listen(PasswordReset::class, function (): void {
            throw new RuntimeException('Falha ao processar evento.');
        });
        $this->api('POST', '/api/password/reset', $this->resetData($user, $code))->assertStatus(500);
        $this->assertTrue(Hash::check('senha-segura', $user->refresh()->password));
        $this->assertSame($rememberToken, $user->remember_token);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email, 'attempts' => 0]);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseCount('access_grant_tokens', 1);
        $this->assertTrue($grant->refresh()->isActive());
        Event::fake([PasswordReset::class]);
        $this->api('POST', '/api/password/reset', $this->resetData($user, $code))->assertOk();
    }

    private function requestCode(User $user): string
    {
        $this->api('POST', '/api/password/forgot', ['email' => $user->email])->assertOk();
        Notification::assertSentTo($user, PasswordResetCodeNotification::class);

        return Notification::sent($user, PasswordResetCodeNotification::class)->last()->code;
    }

    private function resetData(User $user, string $code): array
    {
        return [
            'email' => $user->email, 'code' => $code,
            'password' => 'nova-senha-segura', 'password_confirmation' => 'nova-senha-segura',
        ];
    }
}
