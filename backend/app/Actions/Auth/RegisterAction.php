<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RegisterAction
{
    public function __construct(private readonly LoginAction $login) {}

    /**
     * @param  array{name: string, email: string, username: string, password: string, device_name?: string}  $data
     * @return array{token: string, user: User}
     */
    public function execute(array $data): array
    {
        try {
            return DB::transaction(function () use ($data): array {
                User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'username' => $data['username'],
                    'password' => $data['password'],
                ]);

                return $this->login->execute($data['email'], $data['password'], $data['device_name'] ?? 'mobile');
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            // Validation can race with another registration; the DB remains authoritative.
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
