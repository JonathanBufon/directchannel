<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use App\Http\Requests\Users\ApiRequest;

final class ChangePasswordRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password:sanctum'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
