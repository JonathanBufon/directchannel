<?php

declare(strict_types=1);

namespace App\Http\Requests\Password;

use App\Http\Requests\Users\ApiRequest;

final class ForgotPasswordRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['email' => ['required', 'string', 'email', 'max:255']];
    }
}
