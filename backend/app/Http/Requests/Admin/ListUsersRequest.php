<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

final class ListUsersRequest extends AdminRequest
{
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['ativos', 'desativados', 'bloqueados'])],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
