<?php

declare(strict_types=1);

namespace App\Actions\Password;

use App\Auth\PasswordResetCodes;
use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

final class ForgotPasswordAction
{
    public function __construct(private readonly PasswordResetCodes $codes) {}

    public function execute(string $email): void
    {
        DB::transaction(function () use ($email): void {
            // Serialize issuance and reset on the same active account.
            $user = User::where('email', $email)->lockForUpdate()->first();
            if ($user === null) {
                return;
            }

            $notificationKey = 'password-reset-notification:'.$user->id;
            if (RateLimiter::tooManyAttempts($notificationKey, 1)) {
                return;
            }

            $createdAt = DB::table('password_reset_tokens')->where('email', $email)->value('created_at');
            if ($createdAt !== null && Carbon::parse($createdAt)->addSeconds(60)->isFuture()) {
                return;
            }

            $user->notify(new PasswordResetCodeNotification($this->codes->issue($user)));
            // Consumption removes the code, but must not remove the mail cooldown.
            RateLimiter::hit($notificationKey, 60);
        }, 3);
    }
}
