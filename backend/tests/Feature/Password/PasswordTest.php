<?php

declare(strict_types=1);

namespace Tests\Feature\Password;

use App\Models\AccessGrantToken;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\Feature\Users\ApiTestCase;

final class PasswordTest extends ApiTestCase
{
    public function test_forgot_always_has_same_generic_success_and_broker_stores_only_hashed_token(): void
    {
        Notification::fake();
        $user = $this->user();
        $deleted = $this->user();
        $deleted->delete();
        $response = $this->api('POST', '/api/password/forgot', ['email' => $user->email])->assertOk();
        $this->api('POST', '/api/password/forgot', ['email' => 'inexistente@example.com'])->assertOk()->assertExactJson($response->json());
        $this->api('POST', '/api/password/forgot', ['email' => $deleted->email])->assertOk()->assertExactJson($response->json());
        $this->api('POST', '/api/password/forgot', ['email' => $user->email])->assertOk()->assertExactJson($response->json());
        Notification::assertSentToTimes($user, ResetPassword::class, 1);
        Notification::assertNotSentTo($deleted, ResetPassword::class);
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): bool {
            $stored = DB::table('password_reset_tokens')->where('email', $user->email)->first();
            $this->assertNotSame($notification->token, $stored->token);
            $this->assertTrue(Hash::check($notification->token, $stored->token));
            $mail = $notification->toMail($user);
            $this->assertSame('Redefinir senha do DirectChannel', $mail->subject);
            $this->assertContains($notification->token, $mail->introLines);

            return true;
        });
    }

    public function test_forgot_renders_real_portuguese_mail_without_a_web_reset_route(): void
    {
        config(['mail.default' => 'array']);
        $this->api('POST', '/api/password/forgot', ['email' => $this->user()->email])->assertOk();
        $this->assertDatabaseCount('password_reset_tokens', 1);
    }

    public function test_forgot_validates_email_and_throttles_requests(): void
    {
        $this->api('POST', '/api/password/forgot', ['email' => 'inválido'])->assertUnprocessable()->assertJsonValidationErrors('email');
        for ($i = 0; $i < 5; $i++) {
            $this->api('POST', '/api/password/forgot', ['email' => 'inexistente@example.com'])->assertOk();
        }
        $this->api('POST', '/api/password/forgot', ['email' => 'inexistente@example.com'])->assertStatus(429);
    }

    public function test_reset_consumes_broker_token_changes_password_and_revokes_all_access(): void
    {
        Notification::fake();
        Event::fake([PasswordReset::class]);
        $user = $this->user();
        $oldBearer = $this->signIn($user);
        $grant = $user->accessGrants()->active()->firstOrFail();
        $extra = $user->createToken('Extra');
        AccessGrantToken::create(['access_grant_id' => $grant->id, 'personal_access_token_id' => $extra->accessToken->id]);
        $this->token = null;
        $this->api('POST', '/api/password/forgot', ['email' => $user->email])->assertOk();
        $notification = Notification::sent($user, ResetPassword::class)->first();
        $data = ['email' => $user->email, 'token' => $notification->token, 'password' => 'nova-senha-segura', 'password_confirmation' => 'nova-senha-segura'];
        $this->api('POST', '/api/password/reset', $data)->assertOk();
        $this->assertTrue(Hash::check('nova-senha-segura', $user->refresh()->password));
        $this->assertDatabaseCount('password_reset_tokens', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseCount('access_grant_tokens', 0);
        $this->assertFalse($grant->refresh()->isActive());
        Event::assertDispatched(PasswordReset::class);
        $this->api('POST', '/api/password/reset', $data)->assertUnprocessable()->assertJsonValidationErrors('token');
        $this->api('GET', '/api/me', [], $oldBearer)->assertUnauthorized();
        $this->api('POST', '/api/login', ['email' => $user->email, 'password' => 'nova-senha-segura', 'device_name' => 'Celular'])->assertOk();
    }

    public function test_reset_validates_input_and_rejects_unknown_expired_mismatched_or_invalid_token(): void
    {
        $user = $this->user();
        $token = Password::createToken($user);
        $valid = ['email' => $user->email, 'token' => $token, 'password' => 'nova-senha-segura', 'password_confirmation' => 'nova-senha-segura'];
        $this->api('POST', '/api/password/reset')->assertUnprocessable()->assertJsonValidationErrors(['email', 'token', 'password']);
        $this->api('POST', '/api/password/reset', array_replace($valid, ['password_confirmation' => 'outra']))->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->api('POST', '/api/password/reset', array_replace($valid, ['password' => 'curta', 'password_confirmation' => 'curta']))->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->api('POST', '/api/password/reset', array_replace($valid, ['token' => 'incorreto']))->assertUnprocessable()->assertJsonValidationErrors('token');
        $this->api('POST', '/api/password/reset', array_replace($valid, ['email' => $this->user()->email]))->assertUnprocessable()->assertJsonValidationErrors('token');
        DB::table('password_reset_tokens')->where('email', $user->email)->update(['created_at' => now()->subMinutes(61)]);
        $this->api('POST', '/api/password/reset', $valid)->assertUnprocessable()->assertJsonValidationErrors('token');
        $this->assertTrue(Hash::check('senha-segura', $user->refresh()->password));
    }

    public function test_reset_does_not_accept_a_token_for_a_deactivated_account(): void
    {
        $user = $this->user();
        $token = Password::createToken($user);
        $user->delete();
        $this->api('POST', '/api/password/reset', ['email' => $user->email, 'token' => $token, 'password' => 'nova-senha-segura', 'password_confirmation' => 'nova-senha-segura'])->assertUnprocessable()->assertJsonValidationErrors('token');
    }

    public function test_reset_unknown_email_uses_the_same_invalid_token_error_and_is_throttled(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->api('POST', '/api/password/reset', ['email' => 'inexistente@example.com', 'token' => 'inválido', 'password' => 'nova-senha-segura', 'password_confirmation' => 'nova-senha-segura'])->assertUnprocessable()->assertJsonValidationErrors('token');
        }
        $this->api('POST', '/api/password/reset')->assertStatus(429);
    }
}
