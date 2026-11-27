<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateProfileAction
{
    public function __construct(private readonly VerifyCurrentPasswordAction $verifyPassword) {}

    public function execute(User $user, array $data): User
    {
        try {
            return DB::transaction(function () use ($user, $data): User {
                $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                if (isset($data['email']) && $data['email'] !== $locked->email) {
                    $this->verifyPassword->execute($locked, $data['current_password'] ?? '');
                    $locked->email_verified_at = null;
                }
                $locked->fill(Arr::only($data, ['name', 'username', 'email']))->save();

                return $locked;
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            if (str_contains($exception->getMessage(), 'users_username_lower_unique')) {
                throw ValidationException::withMessages(['username' => 'Este nome de usuário já está em uso.']);
            }
            if (str_contains($exception->getMessage(), 'users_email_unique')) {
                throw ValidationException::withMessages(['email' => 'Este e-mail já está em uso.']);
            }
            throw $exception;
        }
    }
}
