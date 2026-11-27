<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\AccessGrantToken;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class LoginAction
{
    /** @return array{token: string, user: User} */
    public function execute(string $email, string $password, string $deviceName): array
    {
        return DB::transaction(function () use ($email, $password, $deviceName): array {
            // All grant mutations serialize on the user, including first login.
            $user = User::withTrashed()->where('email', $email)->lockForUpdate()->first();
            if ($user === null || ! Hash::check($password, $user->password)) {
                throw ValidationException::withMessages(['email' => 'E-mail ou senha inválidos.']);
            }
            if ($user->trashed()) {
                throw new HttpException(403, 'Esta conta foi desativada.');
            }
            if ($user->isBlocked()) {
                throw new HttpException(403, 'Esta conta está bloqueada para novos acessos.');
            }

            foreach ($user->accessGrants()->active()->lockForUpdate()->get() as $previous) {
                $tokenIds = $previous->accessGrantTokens()->pluck('personal_access_token_id');
                // Delete the bridge first because historical FKs use RESTRICT.
                $previous->accessGrantTokens()->delete();
                PersonalAccessToken::whereIn('id', $tokenIds)->delete();
                $previous->update(['revoked_at' => now()]);
            }

            $grant = $user->accessGrants()->create(['started_at' => now()]);
            $expiration = (int) config('sanctum.expiration');
            $token = $user->createToken($deviceName, ['*'], now()->addMinutes($expiration));
            AccessGrantToken::create([
                'personal_access_token_id' => $token->accessToken->getKey(),
                'access_grant_id' => $grant->id,
            ]);

            return ['token' => $token->plainTextToken, 'user' => $user];
        }, 3);
    }
}
