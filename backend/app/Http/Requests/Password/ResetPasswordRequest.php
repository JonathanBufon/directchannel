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
            'code' => ['required', 'string', 'regex:/^\d{6}$/D'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'code.regex' => 'O código deve conter exatamente 6 dígitos.',
        ]);
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), ['code' => 'código de recuperação']);
    }
}
