<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class VerifyCurrentPasswordAction
{
    public function execute(User $user, string $password, string $field = 'current_password'): void
    {
        if (! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([$field => 'A senha atual está incorreta.']);
        }
    }
}
