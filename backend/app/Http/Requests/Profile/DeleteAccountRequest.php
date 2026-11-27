<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use App\Http\Requests\Users\ApiRequest;

final class DeleteAccountRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['password' => ['required', 'string', 'current_password:sanctum']];
    }
}
