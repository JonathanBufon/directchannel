<?php

declare(strict_types=1);

namespace App\Actions\Password;

use App\Actions\Profile\RevokeTokensAction;
use App\Auth\PasswordResetCodes;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class ResetPasswordAction
{
    public function __construct(
        private readonly PasswordResetCodes $codes,
        private readonly RevokeTokensAction $revokeTokens,
    ) {}

    public function execute(array $data): void
    {
        $reset = DB::transaction(function () use ($data): bool {
            $user = User::where('email', $data['email'])->lockForUpdate()->first();
            if ($user === null || ! $this->codes->consume($data['email'], $data['code'])) {
                return false;
            }

            $user->forceFill(['password' => $data['password'], 'remember_token' => Str::random(60)])->save();
            $this->revokeTokens->execute($user);
            event(new PasswordReset($user));

            return true;
        }, 3);

        // Commit failed attempts before returning the validation error.
        if (! $reset) {
            throw ValidationException::withMessages(['code' => 'Código inválido ou expirado.']);
        }
    }
}
