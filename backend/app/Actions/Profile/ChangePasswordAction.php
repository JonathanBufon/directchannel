<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ChangePasswordAction
{
    public function __construct(
        private readonly VerifyCurrentPasswordAction $verifyPassword,
        private readonly RevokeTokensAction $revokeTokens,
    ) {}

    public function execute(User $user, string $currentPassword, string $password): void
    {
        DB::transaction(function () use ($user, $currentPassword, $password): void {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $this->verifyPassword->execute($locked, $currentPassword);
            $locked->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            DB::table('password_reset_tokens')->where('email', $locked->email)->delete();
            $this->revokeTokens->execute($locked, (int) $user->currentAccessToken()->getKey());
        }, 3);
    }
}
