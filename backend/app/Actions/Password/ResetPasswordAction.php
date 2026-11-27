<?php

declare(strict_types=1);

namespace App\Actions\Password;

use App\Actions\Profile\RevokeTokensAction;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class ResetPasswordAction
{
    public function __construct(private readonly RevokeTokensAction $revokeTokens) {}

    public function execute(array $data): void
    {
        DB::transaction(function () use ($data): void {
            // Serialize before broker validation, so two requests cannot consume one token.
            User::where('email', $data['email'])->lockForUpdate()->first();
            $status = Password::reset($data, function (User $user, string $password): void {
                $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
                $this->revokeTokens->execute($user);
                event(new PasswordReset($user));
            });
            if ($status !== Password::PASSWORD_RESET) {
                throw ValidationException::withMessages(['token' => 'Código de recuperação inválido ou expirado.']);
            }
        }, 3);
    }
}
