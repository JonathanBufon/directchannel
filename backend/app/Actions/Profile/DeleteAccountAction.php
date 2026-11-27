<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DeleteAccountAction
{
    public function __construct(
        private readonly VerifyCurrentPasswordAction $verifyPassword,
        private readonly RevokeTokensAction $revokeTokens,
    ) {}

    public function execute(User $user, string $password): void
    {
        DB::transaction(function () use ($user, $password): void {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $this->verifyPassword->execute($locked, $password, 'password');
            $this->revokeTokens->execute($locked);
            DB::table('password_reset_tokens')->where('email', $locked->email)->delete();
            $locked->delete();
        }, 3);
    }
}
