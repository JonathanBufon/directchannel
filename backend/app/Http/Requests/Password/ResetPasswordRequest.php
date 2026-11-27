<?php

declare(strict_types=1);

namespace App\Http\Requests\Password;

use App\Http\Requests\Users\ApiRequest;

final class ResetPasswordRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'token' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
