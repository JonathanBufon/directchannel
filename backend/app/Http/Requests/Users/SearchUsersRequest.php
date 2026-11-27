<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

final class SearchUsersRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['search' => ['required', 'string', 'min:2', 'max:255'], 'page' => ['sometimes', 'integer', 'min:1']];
    }
}
