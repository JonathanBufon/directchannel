<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Closure;
use Illuminate\Support\Facades\DB;

final class RegisterRequest extends AuthRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'username' => [
                'bail', 'required', 'string', 'min:3', 'max:30', 'regex:/\A[A-Za-z0-9_.]+\z/',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (DB::table('users')->whereRaw('lower(username) = lower(?)', [$value])->exists()) {
                        $fail('Este nome de usuário já está em uso.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'device_name' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }
}
